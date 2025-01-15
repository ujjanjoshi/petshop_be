<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Redeemer;
use App\Traits\ResellerApi;

class CertificateService
{
    use ResellerApi;

    /**
     * @param string|array $config
     * @throw Exception
     */
    public function __construct()
    {
        $this->petInit ();
    }
    /**
     * Update the travel date
     *
     * @return mixed $certificate or false
     */
    public function updateTravelDate(Certificate $certificate)
    {
        $retval = $this->petRequest ('POST', 'certificates/'. $certificate->code .'/travel-date', [
            'json' => [ 'start_date' => $certificate->start_date, 'end_date'  => $certificate->end_date],
        ]);
        return $retval === false ? $retval : $certificate;
    }
    /**
     * Transfer the Certificate to the new redeemer
     *
     * @return mixed $certificate or false
     */
    public function transfer(Certificate $certificate, array $newRedeemer)
    {
        $retval = $this->petRequest('POST', 'certificates/'. $certificate->code ."/transfer", [
            'json' => $newRedeemer
        ]);
        return $retval === false ? $retval : $retval;
    }
    /**
     * Get redemption pdf form  of the certificate
     *
     * @return pdf certificate
     */
    public function getRedemption(Certificate $certificate)
    {
        return $this->petRequest ('GET', 'certificates/'. $certificate->code .'/pdf');
    }
    /**
     * Get confirmation pdf letter of the certificate after fulfilled
     *
     * @return pdf certificate
     */
    public function getConfirmationLetter(Certificate $certificate)
    {
        return $this->petRequest ('GET', 'fulfillments/'. $certificate->code .'/confirmation-letter');
    }
    /**
     * List existing guest list associated with this certificate
     *
     * @return array $guests
     */
    public function listGuest(Certificate $certificate)
    {
        return $this->petRequest('GET', 'fulfillments/'. $certificate->code .'/guests');
    }
    /**
     *
     * @return json $guest
     */
    public function addGuest(Certificate $certificate, array $guest)
    {
        return $this->petRequest('POST', 'fulfillments/'. $certificate->code .'/guests', [ 
            'json' => $guest
        ]);
    }
    /**
     *
     * @return json $guest
     */
    public function updateGuest(Certificate $certificate, int $guest_id, array $guest)
    {
        return $this->petRequest('PUT', 'fulfillments/'. $certificate->code .'/guests/'. $guest_id, [
            'json' => $guest
        ]);
    }
    /**
     *
     * @return json $guest
     */
    public function deleteGuest(Certificate $certificate, int $guest_id)
    {
        return $this->petRequest('DELETE', 'fulfillments/'. $certificate->code .'/guests/'. $guest_id);
    }
}
