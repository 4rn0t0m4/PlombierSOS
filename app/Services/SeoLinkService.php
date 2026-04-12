<?php

namespace App\Services;

use App\Models\City;
use App\Models\Department;
use Illuminate\Support\Collection;

class SeoLinkService
{
    private ?Collection $departments = null;

    private ?Collection $cities = null;

    /**
     * Replace city and department names in HTML text with links.
     */
    public function addLinks(string $html): string
    {
        $html = $this->linkDepartments($html);
        $html = $this->linkCities($html);

        return $html;
    }

    private function linkDepartments(string $html): string
    {
        if (! $this->departments) {
            $this->departments = Department::select('number', 'name', 'slug')
                ->orderByRaw('LENGTH(name) DESC')
                ->get();
        }

        foreach ($this->departments as $dept) {
            $name = preg_quote($dept->name, '/');
            // Only match if not already inside a link
            $html = preg_replace(
                '/(?<!["\/\w>])(' . $name . ')(?![^<]*<\/a>)/u',
                '<a href="/' . $dept->slug . '" class="text-blue-600 hover:underline">$1</a>',
                $html,
                1 // Only first occurrence
            );
        }

        return $html;
    }

    private function linkCities(string $html): string
    {
        if (! $this->cities) {
            $this->cities = City::select('name', 'slug', 'department')
                ->has('plumbers')
                ->where('population', '>', 5000)
                ->orderByRaw('LENGTH(name) DESC')
                ->get()
                ->unique('name');
        }

        foreach ($this->cities as $city) {
            $dept = $this->departments?->firstWhere('number', $city->department);
            if (! $dept) {
                continue;
            }

            $name = preg_quote($city->name, '/');
            // Only match if not already inside a link
            $html = preg_replace(
                '/(?<!["\/\w>])(' . $name . ')(?![^<]*<\/a>)/u',
                '<a href="/' . $dept->slug . '/' . $city->slug . '" class="text-blue-600 hover:underline">$1</a>',
                $html,
                1
            );
        }

        return $html;
    }
}
