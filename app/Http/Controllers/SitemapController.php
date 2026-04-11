<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Plumber;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $plumbers = Plumber::active()->with('cityRelation.departmentRelation')->get();
        $departments = Department::all();
        $cities = City::has('plumbers')->with('departmentRelation')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Pages statiques
        $staticPages = [
            ['url' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => url('/recherche'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => url('/urgence'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => url('/demande'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $xml .= '<url>';
            $xml .= '<loc>'.$page['url'].'</loc>';
            $xml .= '<changefreq>'.$page['changefreq'].'</changefreq>';
            $xml .= '<priority>'.$page['priority'].'</priority>';
            $xml .= '</url>';
        }

        // Plombiers
        foreach ($plumbers as $plumber) {
            $xml .= '<url>';
            $xml .= '<loc>'.url($plumber->url).'</loc>';
            $xml .= '<lastmod>'.$plumber->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // Départements
        foreach ($departments as $dept) {
            $xml .= '<url>';
            $xml .= '<loc>'.url('/'.$dept->slug).'</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.6</priority>';
            $xml .= '</url>';
        }

        // Villes
        foreach ($cities as $city) {
            $deptSlug = $city->departmentRelation?->slug;
            if (! $deptSlug) {
                continue;
            }
            $xml .= '<url>';
            $xml .= '<loc>'.url('/'.$deptSlug.'/'.$city->slug).'</loc>';
            $xml .= '<lastmod>'.$city->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
