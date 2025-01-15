<?php

namespace App\Http\Controllers\PetShop;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\ExperienceLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ExperiencesController extends Controller
{
    public function categories()
    {
        $categories_collection_id = [19, 28, 34, 0];
        $unique_objects = [];
        $encountered_names = [];
        foreach ($categories_collection_id as $id) {
            switch ($id) {
                case 19:
                    $collection = "The Sport Collection";
                    break;
                case 28:
                    $collection = "The Experiential Collection";
                    break;
                case 34:
                    $collection = "The Travel Collection";
                    break;
                case 0:
                    $collection = "Tier Level Awards";
                    break;
                default:
                    $collection = "";
            }

            $category = ExperienceCategory::where('parent_id', $id)->get();

            foreach ($category as $object) {
                $name = strtolower(rtrim($object->name, 's'));
                if ($object->category_id == 72 && $object->parent_id == 0 && $object->category_id != 56) {
                    $unique_objects[] = [
                        "collection_name" => "Travel Voucher",
                        "image_url" => $object->image,
                        "name" => $object->name
                    ];
                }
                if ($object->category_id != 56 && $object->parent_id == 0) {
                    $encountered_names[] = $name;
                }

                if ($object->category_id == 56 && $object->parent_id == 0 && $object->category_id != 72) {
                    $name_data = "Level 41-50";
                    if (!in_array($name_data, $encountered_names)) {
                        $encountered_names[] = $name;
                        $data = [
                            [
                                "collection_name" => $collection,
                                "image_url" => $object->image,
                                "name" => "Level 1-10",
                            ],
                            [
                                "collection_name" => $collection,
                                "image_url" => $object->image,
                                "name" => "Level 11-20",
                            ],
                            [
                                "collection_name" => $collection,
                                "image_url" => $object->image,
                                "name" => "Level 21-30",
                            ],
                            [
                                "collection_name" => $collection,
                                "image_url" => $object->image,
                                "name" => "Level 31-40",
                            ],
                            [
                                "collection_name" => $collection,
                                "image_url" => $object->image,
                                "name" => "Level 41-50",
                            ],
                        ];

                        foreach ($data as $item) {
                            $unique_objects[] = $item;
                            $encountered_names[] = $item['name'];
                        }
                    }
                    continue;
                }

                if (!in_array($name, $encountered_names)) {
                    $encountered_names[] = $name;
                    $unique_objects[] = [
                        "collection_name" => $collection,
                        "image_url" => $object->image,
                        "name" => $object->name
                    ];
                }
            }
        }

        return ($unique_objects);
    }

    public function sportCollection()
    {
        $categories_collection_id = 19;
        $unique_objects = [];
        $encountered_names = [];
        $collection = "The Sport Collection";
        $category = ExperienceCategory::select(['name', 'image'])->where('parent_id', $categories_collection_id)->get();
        foreach ($category as $object) {
            $name = strtolower(rtrim($object->name, 's'));


            if (!in_array($name, $encountered_names)) {
                $encountered_names[] = $name;
                $unique_objects[] = [
                    "collection_name" => $collection,
                    "image_url" => $object->image,
                    "name" => $object->name
                ];
            }
        }

        return (array_values($unique_objects));
    }

    public function travelCollection()
    {
        $categories_collection_id = 34;
        $unique_objects = [];
        $encountered_names = [];
        $collection = "The Travel Collection";
        $category = ExperienceCategory::select(['name', 'image'])->where('parent_id', $categories_collection_id)->get();
        foreach ($category as $object) {
            $name = strtolower(rtrim($object->name, 's'));


            if (!in_array($name, $encountered_names)) {
                $encountered_names[] = $name;
                $unique_objects[] = [
                    "collection_name" => $collection,
                    "image_url" => $object->image,
                    "name" => $object->name
                ];
            }
        }

        return (array_values($unique_objects));
    }

    public function experientialCollection()
    {
        $categories_collection_id = 28;
        $unique_objects = [];
        $encountered_names = [];
        $collection = "The Experiential Collection";
        $category = ExperienceCategory::select(['name', 'image'])->where('parent_id', $categories_collection_id)->get();
        foreach ($category as $object) {
            $name = strtolower(rtrim($object->name, 's'));


            if (!in_array($name, $encountered_names)) {
                $encountered_names[] = $name;
                $unique_objects[] = [
                    "collection_name" => $collection,
                    "image_url" => $object->image,
                    "name" => $object->name
                ];
            }
        }

        return (array_values($unique_objects));
    }


    public function travelVoucherCollection()
    {
        $categories_collection_id = 0;
        $unique_objects = [];
        $collection = "Travel Voucher";
        $category = ExperienceCategory::where('parent_id', $categories_collection_id)->get();
        foreach ($category as $object) {
            if ($object->category_id == 72 && $object->parent_id == 0) {
                $unique_objects[] = [
                    "collection_name" =>  $collection,
                    "image_url" => $object->image,
                    "name" => $object->name
                ];
            }
        }

        return (array_values($unique_objects));
    }

    public function tierLevelAwardsCollection()
    {
        $id = [0];
        $unique_objects = [];
        $encountered_names = [];
        $collection = "Tier Level Awards";


        $category = ExperienceCategory::where('parent_id', $id)->get();
        // dd($category);

        foreach ($category as $object) {
            $name = strtolower(rtrim($object->name, 's'));

            if ($object->category_id != 56 && $object->parent_id == 0) {
                $encountered_names[] = $name;
            }

            if ($object->category_id == 56 && $object->parent_id == 0) {
                $name_data = "Level 41-50";
                if (!in_array($name_data, $encountered_names)) {
                    $encountered_names[] = $name;
                    $data = [
                        [
                            "collection_name" => $collection,
                            "image_url" => $object->image,
                            "name" => "Level 1-10",
                        ],
                        [
                            "collection_name" => $collection,
                            "image_url" => $object->image,
                            "name" => "Level 11-20",
                        ],
                        [
                            "collection_name" => $collection,
                            "image_url" => $object->image,
                            "name" => "Level 21-30",
                        ],
                        [
                            "collection_name" => $collection,
                            "image_url" => $object->image,
                            "name" => "Level 31-40",
                        ],
                        [
                            "collection_name" => $collection,
                            "image_url" => $object->image,
                            "name" => "Level 41-50",
                        ],
                    ];

                    foreach ($data as $item) {
                        $unique_objects[] = $item;
                        $encountered_names[] = $item['name'];
                    }
                }
                continue;
            }

            if (!in_array($name, $encountered_names)) {
                $encountered_names[] = $name;
                $unique_objects[] = [
                    "collection_name" => $collection,
                    "image_url" => $object->image,
                    "name" => $object->name
                ];
            }
        }


        return ($unique_objects);
    }

    public function getExperiences(Request $request, $endpoint, $search = null)
    {
        $pathInfo = $request->path();
        $pathInfoWithoutPrefix = substr($pathInfo, strlen('categories/'));
        if (in_array($pathInfoWithoutPrefix, ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {

            $name = str_replace('-', ' ', "Tier-Level-Awards");
            // dd($name);
        } else {
            $name = str_replace('-', ' ', $endpoint);
            // dd($name);
        }
        $experienceCategory = ExperienceCategory::select(['name', 'image'])->where('name', $name)->first();

        if (strpos($pathInfoWithoutPrefix, '/') !== false) {

            $array = explode('/', $pathInfoWithoutPrefix);
            $category = str_replace('-', ' ', $array[0]);
            // dd($category);
            $real_endpoint = $array[0];

            if (in_array($array[0], ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {
                $category = "Tier-Level-Awards";
            }

            $search = str_replace('-', ' ', $array[1]);
            // dd($search);
            $categories = ExperienceCategory::whereIn('name', [$category, rtrim($category, 's')])
                ->with(['experiences' => function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('locations', function ($locationQuery) use ($search) {
                            $locationQuery->where('name', 'like', '%' . $search . '%');
                        });
                }])
                ->get();
            $data = [];
            foreach ($categories as $category) {
                // dd($category->experiences);
                /*
                $experience=$category->experiences;
                if ($experience->locations) {
                    $location=$experience->locations;
                    dd($location);
                    if (strpos(strtolower($location->name), strtolower($search)) !== false || strpos(strtolower($location->city), strtolower($search)) !== false || strpos(strtolower($location->state), strtolower($search)) !== false || strpos($location->country, $search) !== false) {

                        $experience->locations = $location->name;
                        $experience->category_image_url = $category->image;
                        $data[] = $experience;
                    }
                    else {
                        // dd(strpos(strtolower($experience->name), $search) );
                        if (strpos(strtolower($experience->name), strtolower($search)) !== false) {
                            $experience->category_image_url = $category->image;
                            $data[] = $experience;
                        }
                    }
                } else {
                    if (strpos(strtolower($experience->name), strtolower($search)) !== false) {
                        $experience->category_image_url = $category->image;
                        $data[] = $experience;
                    }
                }
*/
                foreach ($category->experiences as $experience) {
                    $data[] = $experience;
                }
            }
            $outputArray = collect($data)->unique('id')->values();

            $outputArray = [];
            foreach ($data as $item) {
                if ($item->id != null) {
                    $id = $item->id;
                    if (!isset($outputArray[$id])) {
                        $outputArray[$id] = $item;
                    }
                }
            }

            if ($pathInfoWithoutPrefix == "Tier-Level-Awards") {
                if ($real_endpoint == "Level-1-10" || $real_endpoint == "Level-11-20" || $real_endpoint == "Level-21-30" || $real_endpoint == "Level-31-40" || $real_endpoint == "Level-41-50") {
                    $level = explode('-', $real_endpoint);
                    $start = ($level[1] - 1) * 10;
                    $end = $level[1] * 10;

                    $slicedData = array_slice($outputArray, $start, 10);
                    $outputArray = array_values($slicedData);
                }
            } else {
                $outputArray = array_values($outputArray);
            }

            $datas = [
                'data' => $outputArray,
                'categories' => $experienceCategory,
                'search' => $search,
            ];
    
            return response()->json($datas);
        } else {
            $realEndpoint = $pathInfoWithoutPrefix;

            if (in_array($pathInfoWithoutPrefix, ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {
                $pathInfoWithoutPrefix = "Tier-Level-Awards";
            }

            $name = str_replace('-', ' ', $pathInfoWithoutPrefix);
            $nameTrim = rtrim($name, 's');

            $categories = ExperienceCategory::with('experiences')->whereIn('name', [$name, $nameTrim])->get();
           
            $data = [];

            foreach ($categories as $category) {
                //    dd($category->experiences['id']);
                foreach ($category->experiences as $experience) {
                    $data[] = [
                        "id" => $experience['experience_id'],
                        "name" => $experience['name'],
                        "sku" => $experience['sku'],
                        "thumbnail" => $experience['thumbnail'],
                    ];
                }
            }
            
            $outputArray = collect($data)->values()->reverse();
            // dd($outputArray);
            if ($pathInfoWithoutPrefix == "Tier-Level-Awards") {
                if (in_array($realEndpoint, ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {
                   if($realEndpoint=="Level-1-10")
                   {
                    $level = explode('-', $realEndpoint);
                    $start = ($level[1] - 1) ;
                    $end = $level[1];
                   
                    $outputArray = $outputArray->slice($start, 9);
                   }else{
                    $level = explode('-', $realEndpoint);
                    $start = ($level[1] - 1) ;
                    $end = $level[1];
                   
                    $outputArray = $outputArray->slice($start-1, 10);
                   }
                   
                }
            }
        }
        $datas = [
            'data' => $outputArray,
            'categories' => $experienceCategory,
            'search' => $search,
        ];

        return response()->json($datas);
        // dd($outputArray);
        // return view('Experiences.index', compact('outputArray', 'experienceCategory', 'search'));
    }

    public function getExperienceDetails($sku)
    {

        $experience = Experience::where('sku', $sku)->first();
        if ($experience == null) {
            return view('errors.404', ['message' => "SKU $sku NOT FOUND"]);
        }
        // dd($experience);
        // $category = str_replace('-', ' ', $categories);
        //$categoriesUrl = ExperienceCategory::select(['image', 'name'])->where('experience_id', $experience->experience_id)
        //    ->first();
        //$category = $categoriesUrl['name'];
        //if ($experience) {
        //    $experience->categories_url = $categoriesUrl['image'];
        //}

        return view('Experiences.details', compact('experience'));
    }

    public function experience()
    {
            $experiencesController = new ExperiencesController();
            $experiential_collection = $experiencesController->experientialCollection();
            $sport_collection = $experiencesController->sportCollection();
            $travel_collection = $experiencesController->travelCollection();
            $travel_voucher_collection=$experiencesController->travelVoucherCollection();
            $data = [
                'experientialCollection' => $experiential_collection,
                'sportCollection' => $sport_collection,
                'travelCollection' => $travel_collection,
                'travel_voucher_collection'=>$travel_voucher_collection
            ];
        return response()->json($data);
    }

    public function getExperiencesLocation(Request $request, $endpoint, $search = null)
    {
        $pathInfo = $request->path();
        $pathInfoWithoutPrefix = substr($pathInfo, strlen('locations/'));
        $name = str_replace('-', ' ', $endpoint);
        $experienceCategory = ExperienceCategory::select(['name', 'image'])->where('name', $name)->first();
        if (strpos($pathInfoWithoutPrefix, '/') !== false) {

            $array = explode('/', $pathInfoWithoutPrefix);
            $category = str_replace('-', ' ', $array[0]);
            // dd($category);
            $real_endpoint = $array[0];

            if (in_array($array[0], ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {
                $category = "Tier-Level-Awards";
            }

            $categories = ExperienceLocation::where('state', $category)
                ->with(['experiences' => function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }])
                ->get();

            // dd($categories);
            $data = [];
            foreach ($categories as $category) {
                // dd($category->experiences);
                /*
                $experience=$category->experiences;
                if ($experience->locations) {
                    $location=$experience->locations;
                    dd($location);
                    if (strpos(strtolower($location->name), strtolower($search)) !== false || strpos(strtolower($location->city), strtolower($search)) !== false || strpos(strtolower($location->state), strtolower($search)) !== false || strpos($location->country, $search) !== false) {

                        $experience->locations = $location->name;
                        $experience->category_image_url = $category->image;
                        $data[] = $experience;
                    }
                    else {
                        // dd(strpos(strtolower($experience->name), $search) );
                        if (strpos(strtolower($experience->name), strtolower($search)) !== false) {
                            $experience->category_image_url = $category->image;
                            $data[] = $experience;
                        }
                    }
                } else {
                    if (strpos(strtolower($experience->name), strtolower($search)) !== false) {
                        $experience->category_image_url = $category->image;
                        $data[] = $experience;
                    }
                }
*/
                foreach ($category->experiences as $experience) {
                    $data[] = $experience;
                }
            }
            $outputArray = collect($data)->unique('id')->values();

            $outputArray = [];
            foreach ($data as $item) {
                if ($item->id != null) {
                    $id = $item->id;
                    if (!isset($outputArray[$id])) {
                        $outputArray[$id] = $item;
                    }
                }
            }
            $outputArray = array_values($outputArray);
            // dd($outputArray);

            return view('Experiences.index', compact('outputArray', 'experienceCategory', 'search', 'name'));
        } else {
            $realEndpoint = $pathInfoWithoutPrefix;
            $name = str_replace('-', ' ', $pathInfoWithoutPrefix);

            $categories = ExperienceLocation::with('experiences')->where('state', $name)->get();
            // dd($categories);
            $data = [];

            foreach ($categories as $category) {
                //    dd($category->experiences['id']);
                foreach ($category->experiences as $experience) {
                    $data[] = [
                        "id" => $experience['experience_id'],
                        "name" => $experience['name'],
                        "sku" => $experience['sku'],
                        "thumbnail" => $experience['thumbnail'],
                    ];
                }
            }
            // dd($data);
            $outputArray = collect($data)->unique('id')->values();
        }
        // dd($outputArray);
        return view('Experiences.index', compact('outputArray', 'experienceCategory', 'search', 'name'));
    }
}
