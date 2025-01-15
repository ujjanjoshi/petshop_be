<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\ExperienceLocation;
use Illuminate\Http\Request;

class ExperienceController extends Controller
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
        }


        return response()->json($unique_objects);
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

    public function getExperiences(Request $request, $endpoint)
    {
        // dd('hlo');
        $search = $request->query('search');
        $pathInfo = $request->path();
        $pathInfoWithoutPrefix = substr($pathInfo, strlen('api/categories/'));
        if (in_array($pathInfoWithoutPrefix, ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {
            $name = str_replace('-', ' ', "Tier-Level-Awards");
        } else {
            $name = str_replace('-', ' ', $endpoint);
            // dd($name);
        }
        $experienceCategory = ExperienceCategory::select(['name', 'image'])->where('name', $name)->first();
        // dd($experienceCategory);
        if ($search != null) {

            $array = explode('/', $pathInfoWithoutPrefix);
            $category = str_replace('-', ' ', $array[0]);
            // dd($category);
            $real_endpoint = $array[0];

            if (in_array($array[0], ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {
                $category = "Tier-Level-Awards";
            }
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
                foreach ($category->experiences as $experience) {
                    $data[] = [
                        "id" => $experience['experience_id'],
                        "name" => $experience['name'],
                        "sku" => $experience['sku'],
                        "thumbnail" => $experience['thumbnail'],
                    ];
                }
            }

            $outputArray = collect($data)->unique('id')->values();
            $outputArray = [];
            foreach ($data as $item) {
                if ($item['id'] != null) {
                    $id = $item['id'];
                    if (!isset($outputArray[$id])) {
                        $outputArray[$id] = $item;
                    }
                }
            }

            if ($pathInfoWithoutPrefix == "Tier-Level-Awards") {
                if ($real_endpoint == "Level-1-10" || $real_endpoint == "Level-11-20" || $real_endpoint == "Level-21-30" || $real_endpoint == "Level-31-40" || $real_endpoint == "Level-41-50") {
                    
                    $level = explode('-', $real_endpoint);
                    $start = ($level[1] - 1);
                    $end = $level[1];

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
            // dd($data);
            $outputArray = collect($data)->reverse()->values();
            if ($pathInfoWithoutPrefix == "Tier-Level-Awards") {
                if (in_array($realEndpoint, ["Level-1-10", "Level-11-20", "Level-21-30", "Level-31-40", "Level-41-50"])) {
                    if($realEndpoint=="Level-1-10")
                    {
                     $level = explode('-', $realEndpoint);
                     $start = ($level[1] - 1) ;
                     $end = $level[1];
                    
                     $outputArray = $outputArray->slice($start, 9)->values();;
                    }else{
                     $level = explode('-', $realEndpoint);
                     $start = ($level[1] - 1) ;
                     $end = $level[1];
                     $outputArray = $outputArray->slice($start-1, 10)->values();;
                    }
                }
            }
        }
        // dd($outputArray);
        $datas = [
            'data' => $outputArray,
            'categories' => $experienceCategory,
            'search' => $search,
        ];

        return response()->json($datas);
    }

    public function getExperienceDetails($sku)
    {

        $experience = Experience::with(['categories' => function ($query) {
            $query->select('id', 'name');
        }])->where('sku', $sku)->first();
        if ($experience == null) {
            return view('errors.404', ['message' => "SKU $sku NOT FOUND"]);
        }
        return response()->json($experience->toArray());
    }

    public function experience()
    {
        $experiencesController = new ExperienceController();
        $experiential_collection = $experiencesController->experientialCollection();
        $sport_collection = $experiencesController->sportCollection();
        $travel_collection = $experiencesController->travelCollection();
        $data = [
            'experientialCollection' => $experiential_collection,
            'sportCollection' => $sport_collection,
            'travelCollection' => $travel_collection
        ];
        return response()->json($data);
    }

    public function getExperiencesLocation(Request $request, $endpoint, $search = null)
    {
        $pathInfo = $request->path();
        $pathInfoWithoutPrefix = substr($pathInfo, strlen('api/locations/'));
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
            $datas = [
                'data' => $outputArray,
                'categories' => $experienceCategory,
                'search' => $search,
                'name' => $name
            ];

            return response()->json($datas);
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
        $datas = [
            'data' => $outputArray,
            'categories' => $experienceCategory,
            'search' => $search,
            'name' => $name
        ];

        return response()->json($datas);
    }

    public function globalSearch(Request $request)
    {
        $search = $request->query('search');
        $data = [];
        $category = [];
        $location = [];
        $experiences = Experience::select(['id', 'sku', 'name'])
            ->where('name', 'like', "%$search%")
            ->take(5)
            ->get();
        $categories = ExperienceCategory::select(['name'])->where('name', 'like', "%$search%")
            ->take(5)
            ->distinct()
            ->get();
        $location_data = ExperienceLocation::select(['state'])->where('state', 'like', "%$search%")
            ->take(5)
            ->distinct()
            ->get();
        if (count($experiences) > 0) {
            foreach ($experiences as $experience) {
                // dd($experience);
                $data[] = [
                    "name" => $experience->name,
                    "sku" => $experience->sku,

                ];
            }
        }

        if (count($location_data) > 0) {
            foreach ($location_data as $location_datas) {
                // dd($experience);
                $location[] = [
                    "name" => $location_datas->state,
                    "type" => 'location',

                ];
            }
        }
        $filteredCategories = [];
        if (count($categories) > 0) {
            foreach ($categories as $categories_data) {
                // if($categories_data->name!="The Sports Collection" || $categories_data->name!="The Experiential Collection"){
                $category[] = [
                    "name" => $categories_data->name,
                    "type" => 'category',
                ];
                $filteredCategories = array_filter($category, function ($category) {
                    // Gift cards
                    return stripos($category['name'], 'Collection') === false && stripos($category['name'], 'Tier Level Choices') === false && stripos($category['name'], "Tier Level Awards") === false && stripos($category['name'], "Tier Level Award") === false && stripos($category['name'], 'Gift cards') === false && stripos($category['name'], 'Gift card') === false;
                });

                // Convert back to indexed array
                $filteredCategories = array_values($filteredCategories);
            }
        }

        $datas = [
            "experience" => $data,
            "category" => $filteredCategories,
            "location" => $location
        ];
        return response()->json(['data' => $datas]);
    }
}
