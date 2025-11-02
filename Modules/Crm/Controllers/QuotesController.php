<?php

namespace Modules\Crm\Controllers;

use Modules\Quotes\Services\QuoteService;

/**
 * QuotesController (Guest).
 *
 * Guest portal quote viewing
 *
 * @legacy-file application/modules/guest/controllers/Quotes.php
 */
class QuotesController
{
    /**
     * Quote service instance.
     *
     * @var QuoteService
     */
    protected QuoteService $quoteService;

    /**
     * Constructor.
     *
     * @param QuoteService $quoteService
     */
    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function index()
    {
        // Guest quote list
        return view('crm::guest_quotes');
    }

    public function view(string $urlKey)
    {
        // Guest quote view by URL key
        $quote = $this->quoteService->getByUrlKey($urlKey);

        return view('crm::guest_quote_view', ['quote' => $quote]);
    }

    public function approve(string $urlKey)
    {
        // Guest quote approval
        $quote = $this->quoteService->getByUrlKey($urlKey);
        $quote->update(['quote_status_id' => 4]); // Approved

        return redirect()->back()->with('alert_success', trans('quote_approved'));
    }
}
