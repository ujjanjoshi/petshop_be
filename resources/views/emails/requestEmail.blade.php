<x-mail::message>
<h4 style="text-align: center; font-size: 24px; line-height: 28px; font-weight: 500;">
Request Email
</h4>
# Special Ticket Request

The following user just make the following special request.

<x-mail::table>
|       |       |
|-------|-------|
|Name   | {{ $ticketRequest->first_name }} {{ $ticketRequest->last_name }} |
|Email  | {{ $ticketRequest->email }} | 
|Phone  | {{ $ticketRequest->phone_number }} |
|Event  | {{ $ticketRequest->event_name }} |
|# of Tickets | {{ $ticketRequest->no_of_tickets }} |
|Seating Category| {{ $ticketRequest->seating_category }} |
|Special Instruction| {{ $ticketRequest->special_instruction }} |
</x-mail::table>
{!! $body !!}

{{ config('app.name') }}
</x-mail::message>
