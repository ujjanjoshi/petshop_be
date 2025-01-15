<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\TicketDeliveryDetail;
use App\Models\TicketDetail;
use App\Models\TicketFaceValueDetail;
use App\Models\TicketNumberOfTicketsForSale;
use App\Models\TicketProceedPriceDetail;
use App\Models\TicketRestrictionsBenefitsDetail;
use App\Models\TicketSeatDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchTicketList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-ticket-list-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = config('app.peturl') .'/tickets';
        $apiKey = config('app.petapikey');
        $id = config('app.petid');
        if (Cache::has('page_count_ticket_list')) {
            $page_count = Cache::get('page_count_ticket_list');
        } else {
            $page_count = 1;
        }

        $queryParams = [
            'page' => $page_count,
            "productionId" => "01hemmdm70rhy5h81wzetbg7hh",
        ];

        $response = Http::withHeaders([
            'SECURITYTOKEN' => $apiKey,
            'RESELLERID' => $id,
        ])->get($url, $queryParams);

        // Check if the request was successful
        if ($response->successful()) {
            // Data received successfully
            $data = $response->json();
            // dd($data);
            if (count($data['data']) > 0) {
                foreach ($data['data'] as $data) {
                    $etickets = !empty($data['ticket']['etickets']) ? $data['ticket']['etickets'] : null;

                    // Modify the 'etickets' field in the $data array
                    $data['ticket']['etickets'] = $etickets;

                    // Insert ticket
                    if (!empty($data['ticket'])) {
                        $ticket = new Ticket($data['ticket']);
                        $ticket->save();
                        $ticket_id = $ticket->id;
                    }

                    // Insert ticket_number_of_tickets_for_sales
                    if (!empty($data['number_of_tickets_for_sale'])) {
                        $numberOfTicketsForSale = TicketNumberOfTicketsForSale::create($data['number_of_tickets_for_sale']);
                        $ticket_sale_id = $numberOfTicketsForSale->id;
                    }

                    // Insert ticket_seat_details
                    if (!empty($data['seat_details'])) {
                        $seatDetail = TicketSeatDetail::create($data['seat_details']);
                        $seat_details_id = $seatDetail->id;
                    }

                    // Insert ticket_face_value_details
                    if (!empty($data['face_value'])) {
                        $faceValueDetail = TicketFaceValueDetail::create($data['face_value']);
                        $face_value_id = $faceValueDetail->id;
                    }

                    // Insert ticket_proceed_price_details
                    if (!empty($data['proceed_price'])) {
                        $proceedPriceDetail = TicketProceedPriceDetail::create($data['proceed_price']);
                        $proceed_price_id = $proceedPriceDetail->id;
                    }

                    // $options = !empty($data['restrictions_benefits']['options']) ? $data['restrictions_benefits']['options'] : null;

                    // // Check if other is empty and set it to null
                    // $other = !empty($data['restrictions_benefits']['other']) ? $data['restrictions_benefits']['other'] : null;

                    // // Modify the 'options' and 'other' fields in the $data array
                    // $data['restrictions_benefits']['options'] = $options;
                    // $data['restrictions_benefits']['other'] = $other;

                    // // Ensure that $data['restrictions_benefits'] is an array, even if it's null
                    // $restrictionsBenefitsData = $data['restrictions_benefits'] ?? [];

                    // Insert ticket_restrictions_benefits_details
                    if (!empty($data['restrictions_benefits'])) {

                        $restrictionsBenefitsDetail = TicketRestrictionsBenefitsDetail::create([
                            'options' => 0,
                            'other' => 0,
                            // Add any other properties here if needed
                        ]);

                        // Check if the save operation was successful
                        if ($restrictionsBenefitsDetail) {
                            $restrictions_benefits_id = $restrictionsBenefitsDetail->id;
                        }

                    }
                    // Insert ticket_delivery_details
                    if (!empty($data['delivery'])) {
                        $deliveryDetail = TicketDeliveryDetail::create($data['delivery']);
                        $delivery_id = $deliveryDetail->id;
                    }

                    // Insert ticket_details
                    $allData = [
                        "ticket_detail_id" => $data["id"],
                        "seller_id" => $data["seller_id"],
                        "seller_name" => $data["seller_name"],
                        "ticket_id" => $ticket_id ?? null,
                        "event_id" => $data["event"]['id'],
                        "ticket_sale_id" => $ticket_sale_id ?? null,
                        "seat_details_id" => $seat_details_id ?? null,
                        "face_value_id" => $face_value_id ?? null,
                        "proceed_price_id" => $proceed_price_id ?? null,
                        "restrictions_benefits_id" => $restrictions_benefits_id ?? null,
                        "delivery_id" => $delivery_id ?? null,
                        "face_value_percentage" => $data["face_value_percentage"],
                    ];

                    $ticketDetail = TicketDetail::create($allData);
                }


                if (Cache::has('page_count_ticket_list')) {
                    $page_counts = Cache::get('page_count_ticket_list');
                    Cache::put("page_count_ticket_list", $page_counts + 1);
                } else {
                    Cache::put("page_count_ticket_list", $page_count + 1);
                }
            }
        } else {

            $httpStatus = $response->status();
        }
    }
}
