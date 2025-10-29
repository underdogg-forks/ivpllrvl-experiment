<?php

namespace Modules\Crm\Http\Controllers;

/**
 * QuotesController (Guest)
 * 
 * Guest portal quote viewing
 * 
 * @legacy-file application/modules/guest/controllers/Quotes.php
 */
class QuotesController
{
    public function index()
    {
        // Guest quote list
        return view('crm::guest_quotes');
    }
    
    public function view(string $urlKey)
    {
        // Guest quote view by URL key
        $quote = \Modules\Quotes\Entities\Quote::where('quote_url_key', $urlKey)->firstOrFail();
        return view('crm::guest_quote_view', ['quote' => $quote]);
    }
    
    public function approve(string $urlKey)
    {
        // Guest quote approval
        $quote = \Modules\Quotes\Entities\Quote::where('quote_url_key', $urlKey)->firstOrFail();
        $quote->update(['quote_status_id' => 4]); // Approved
        return redirect()->back()->with('alert_success', trans('quote_approved'));
    }
}
