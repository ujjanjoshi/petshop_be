<?php

namespace App\Http\Controllers;

use App\Models\FeatureMerchandise;
use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MerchandiseController extends Controller
{
    public function searchMerchandise(Request $request)
    {
        $query = Merchandise::query()->select(['id', 'name']);

        // Apply search conditions
        $searchTerm = $request->query('search');
        if ($searchTerm) {

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('brand', 'like', "%{$searchTerm}%")
                    ->orWhere('model', 'like', "%{$searchTerm}%")
                    ->orWhere('upc', 'like', "%{$searchTerm}%");
            });

            $query->orWhereHas('merchandiseFeature', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });

            $query->orWhereHas('merchandiseCategory', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }
        $merchandises = $query->take(10)->get();

        $categories = MerchandiseCategory::where('name', 'like', "%{$searchTerm}%")->select(['id', 'name'])->get();
        return response()->json([
            'categories' => $categories,
            'merchandises' => $merchandises,
        ]);
    }
    /**
     * Select either a category or merchandise from the search -- above list
     *
     */
    public function afterSearchMerchandise(Request $request)
    {
        $page = $request->query('page', 1);
        $searchTerm = $request->input('search');

        // merchandise first
        $merchandises = Merchandise::where('name', 'like', $searchTerm . "%")->get();
        if ($merchandises->count() == 0) {
            // try the category -- searchTerm prefix!
            $merchandises = Merchandise::whereHas('merchandiseCategory', function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm . "%");
            })->get();
        }
        if ($merchandises->count() == 1) {
            // TODO: straight into the detail page 
            // redirect ???
        }

        $merchandise_array = [];

        // Paginate the results
        foreach ($merchandises as $merchandise) {
            $merchandise_array[] = $merchandise->toArray();
        }
        $perPage = 20;

        $total = count($merchandise_array); // Total number of items in the array

        // Slice the array to get the items for the current page
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = array_slice($merchandise_array, $offset, $perPage);

        // Create the paginator
        $paginator = new LengthAwarePaginator($itemsForCurrentPage, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
        // dd($paginator);
        $merchandises = $paginator;
        return response()->json([
            'merchandises' => $merchandises,
        ]);
        // return view('Merchandise.afterSearch.merchandise', compact('merchandises'));
    }
    public function merchandiseDetails(Request $request)
    {
        $product_id = $request->query('product_id');

        $merchandises = Merchandise::with(['merchandiseFeature', 'merchandiseCategory', 'merchandiseDimension', 'merchandiseOption', 'merchandiseResource'])->where('product_id', $product_id)->first();
        $children = MerchandiseCategory::where('id', $merchandises->category_id)->first();
        $parent = MerchandiseCategory::where('id', $children->parent_id)->first();
        $grandParent = MerchandiseCategory::where('id', $parent->parent_id)->first();

        return response()->json([
            'children' => $children,
            'parent' => $parent,
            'merchandises' => $merchandises,
            'grandParent' => $grandParent
        ]);
        // return view('Merchandise.details', compact('merchandises'));
    }
    public function listmerchandise(Request $request)
    {
        $featureMerchandises = FeatureMerchandise::select(['merchandise_id'])->get();

        $merchandises = [];
        foreach ($featureMerchandises as $featureMerchandise) {
            $merchandise = Merchandise::where('id', $featureMerchandise->merchandise_id)->select(['id', 'product_id', 'name', 'brand', 'image_lo', 'image_hi', 'selling_price'])->first();
            $merchandises[] = $merchandise->toArray();
        }
        return $merchandises;
    }
    public function getGrandParent()
    {
        // Fetch root categories (grandparents) where parent_id is either null or 0
        $grandParents = MerchandiseCategory::whereNull('parent_id')
            ->whereNotNull('image')
            ->orWhere('parent_id', 0)
            ->get();

        // Collect grandparent IDs
        $grandParentIds = $grandParents->pluck('id')->toArray();

        // Fetch parent categories that belong to the grandparents
        $parents = MerchandiseCategory::whereIn('parent_id', $grandParentIds)->get();

        // Collect parent IDs
        $parentIds = $parents->pluck('id')->toArray();

        // Fetch children categories that belong to the parents and have product_count > 0
        $children = MerchandiseCategory::whereIn('parent_id', $parentIds)
            ->where('product_count', '>', 0)
            ->get();

        // Filter grandparents that have parents and children with product_count > 0
        $grandParent = $grandParents->filter(function ($grandParent) use ($parents, $children) {
            // Find parents of the current grandparent
            $parentCategories = $parents->where('parent_id', $grandParent->id);

            // Check if any of the parent's children have product_count > 0
            $hasValidChildren = $parentCategories->filter(function ($parent) use ($children) {
                return $children->contains('parent_id', $parent->id);
            })->isNotEmpty();

            return $hasValidChildren;
        });

        return $grandParent->values();
    }

    public function getParent(Request $request,$category_id)
    {

        // Get all parent categories where 'parent_id' equals the given category_id
        $parent = MerchandiseCategory::where('parent_id', $category_id)->get();

        // Extract parent category IDs
        $categoryIds = $parent->pluck('id')->toArray();
        $categoryIds[] = $category_id; // Include the main category_id

        // Fetch children categories where 'parent_id' matches one of the parent IDs
        $children = MerchandiseCategory::whereIn('parent_id', $categoryIds)
            ->where('product_count', '>', 0)  // Only include children where product_count is greater than 0
            ->get();

        // Filter parents that have at least one matching child category with product_count > 0
        $filteredParents = $parent->filter(function ($p) use ($children) {
            return $children->contains('parent_id', $p->id);  // Check if any child has this parent ID
        });
        $filteredChildrens = $children->filter(function ($p) use ($parent) {
            return $parent->contains('id', $p->parent_id);  // Check if any child has this parent ID
        });
        $filteredChildrenIds = $filteredChildrens->pluck('id')->toArray();
        $perPage =$request->query('perPage',25);
        $page=$request->query('page',1);
        $product = Merchandise::whereIn('category_id', $filteredChildrenIds)->paginate($perPage, ['*'], 'page', $page);
        $data = [
            'parent' => $filteredParents->values(),
            'product' => $product
        ];
        return $data;
    }

    public function getProduct(Request $request,$category_id)
    {

        // Get all parent categories where 'parent_id' equals the given category_id
        $parent = MerchandiseCategory::where('parent_id', $category_id)->get();

        // Get all category IDs from parent categories including the main category
        $categoryIds = $parent->pluck('id')->toArray(); // Get the parent IDs
        $categoryIds[] = $category_id; // Include the main category_id
        $perPage =$request->query('perPage',25);
        $page=$request->query('page',1);
        $product = Merchandise::whereIn('category_id', $categoryIds)->paginate($perPage, ['*'], 'page', $page);
        // Fetch products where 'category_id' is in the list of the fetched parent categories or the main category
        // $product = Merchandise::whereIn('category_id', $categoryIds)->get();

        // Return the view with the product data
        return $product;
    }

    // public function searchGrandParent($searchTerm)
    // {

    //     // Search for grandparent categories (root categories) where parent_id is either null or 0
    //     $grandParents = MerchandiseCategory::where(function ($query) {
    //         $query->whereNull('parent_id')
    //             ->orWhere('parent_id', 0);
    //      })
    //         ->where('name', 'LIKE', "%{$searchTerm}%")
    //         ->get();

    //     // Collect grandparent IDs
    //     $grandParentIds = $grandParents->pluck('id')->toArray();

    //     // Fetch parent categories that belong to the grandparents
    //     $parents = MerchandiseCategory::whereIn('parent_id', $grandParentIds)
    //         ->where('name', 'LIKE', "%{$searchTerm}%")
    //         ->get();

    //     // Collect parent IDs
    //     $parentIds = $parents->pluck('id')->toArray();

    //     // Fetch children categories that belong to the parents and have product_count > 0
    //     $children = MerchandiseCategory::whereIn('parent_id', $parentIds)
    //         ->where('product_count', '>', 0)
    //         ->get();

    //     // Fetch products in the matching children categories
    //     $product = Merchandise::where('name', 'LIKE', "%{$searchTerm}%")
    //         ->get();

    //     // Filter parents that have children with product_count > 0
    //     $filteredParents = $parents->filter(function ($parent) use ($children) {
    //         // Check if any child has this parent as its parent_id
    //         return $children->contains('parent_id', $parent->id);
    //     });

    //     // Filter grandparents that have filtered parents with children having product_count > 0
    //     $filteredGrandParents = $grandParents->filter(function ($grandParent) use ($filteredParents) {
    //         // Check if any parent has this grandparent as its parent_id
    //         return $filteredParents->contains('parent_id', $grandParent->id);
    //     });

    //     // Return only the grandparents, parents, and products that match and have relevant children/products
    //     return response()->json([
    //         'grandParents' => $filteredGrandParents->values(), // Make sure to reset the keys
    //         'parents' => $filteredParents->values(),
    //         'product' => $product->values(),
    //     ]);
    // }

    public function searchGrandParent($searchTerm)
    {
        // Search for categories where the name matches the search term
        $data = MerchandiseCategory::where('name', 'LIKE', "%{$searchTerm}%")
            ->get();
        $data2 =MerchandiseCategory::get();

        // Filter data into grandparent (root) and parent categories
        $grandParents = $data->filter(function ($category) {
            // Grandparents have no parent_id (null or 0)
            return is_null($category->parent_id) || $category->parent_id == 0;
        });

        // Get all grandparent IDs
        $grandParentIds = $data2->pluck('id')->toArray();

        // Filter parents, whose parent_id is in the grandparent IDs
        $parents = $data->filter(function ($category) use ($grandParentIds) {
            return in_array($category->parent_id, $grandParentIds);
        });

        // Get all parent IDs
        $parentIds = $parents->pluck('id')->toArray();

        // Filter children categories with product_count > 0 whose parent is in the parent IDs
        $children = MerchandiseCategory::whereIn('parent_id', $parentIds)
            ->where('product_count', '>', 0)
            ->get();

        // Filter parents that have children with product_count > 0
        $filteredParents = $parents->filter(function ($parent) use ($children) {
            return $children->contains('parent_id', $parent->id);
        });

        // Filter grandparents that have filtered parents with children having product_count > 0
        $filteredGrandParents = $grandParents->filter(function ($grandParent) use ($filteredParents) {
            return $filteredParents->contains('parent_id', $grandParent->id);
        });
        $product = Merchandise::where('name', 'LIKE', "%{$searchTerm}%")
            ->get();
        // Return the filtered data
        return response()->json([
            'grandParents' => $filteredGrandParents->values(), // Reset keys for response
            'parents' => $filteredParents->values(),
            'product' => $product->values(),
        ]);
    }



    public function searchParent($searchTerm)
    {

        // Search for categories where the name matches the search term
        $data = MerchandiseCategory::where('name', 'LIKE', "%{$searchTerm}%")
            ->get();
        $data2 =MerchandiseCategory::get();

        // Filter data into grandparent (root) and parent categories
        $grandParents = $data->filter(function ($category) {
            // Grandparents have no parent_id (null or 0)
            return is_null($category->parent_id) || $category->parent_id == 0;
        });

        // Get all grandparent IDs
        $grandParentIds = $data2->pluck('id')->toArray();

        // Filter parents, whose parent_id is in the grandparent IDs
        $parents = $data->filter(function ($category) use ($grandParentIds) {
            return in_array($category->parent_id, $grandParentIds);
        });

        // Get all parent IDs
        $parentIds = $parents->pluck('id')->toArray();

        // Filter children categories with product_count > 0 whose parent is in the parent IDs
        $children = MerchandiseCategory::whereIn('parent_id', $parentIds)
            ->where('product_count', '>', 0)
            ->get();

        // Filter parents that have children with product_count > 0
        $filteredParents = $parents->filter(function ($parent) use ($children) {
            return $children->contains('parent_id', $parent->id);
        });

       
        $product = Merchandise::where('name', 'LIKE', "%{$searchTerm}%")
            ->get();

        // Return only the grandparents, parents, and products that match and have relevant children/products
        return response()->json([
            // 'grandParents' => $filteredGrandParents->values(), // Make sure to reset the keys
            'parents' => $filteredParents->values(),
            'product' => $product->values(),
        ]);
    }

    public function searchProduct($searchTerm)
    {
        // Fetch products in the matching children categories
        $product = Merchandise::where('name', 'LIKE', "%{$searchTerm}%")
            ->get();
        // Return only the grandparents, parents, and products that match and have relevant children/products
        return response()->json([
            'product' => $product->values(),
        ]);
    }
}
