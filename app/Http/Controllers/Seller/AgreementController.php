<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AgreementController extends Controller
{
    /**
     * Display the seller agreement page.
     */
    public function index(): Response
    {
        return Inertia::render('Seller/Agreement');
    }
}
