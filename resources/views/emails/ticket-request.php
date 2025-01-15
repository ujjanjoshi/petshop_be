<x-mail::message>
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


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
