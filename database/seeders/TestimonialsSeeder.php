<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['The Last Clicks delivered exceptional coverage for our annual conference. Their professionalism and attention to detail made all the difference.', 'Priya Mehta', 'Marketing Head, Fortune 500 FMCG', 'corporate-events'],
            ['From pre-production to final delivery, their team was seamless. The brand films exceeded our expectations.', 'Arjun Kapoor', 'Creative Director, Leading Ad Agency', 'brands-products'],
            ['Incredible wedding coverage. Every moment was captured beautifully — cinematic, emotional, and authentic.', 'Sneha & Rohit', 'Destination Wedding, Udaipur', 'weddings-celebrations'],
            ['Consistent quality every single time. They truly understand the luxury and automotive space.', 'Vikram Singh', 'Brand Manager, Premium Automobile', 'brands-products'],
        ];
        foreach ($rows as $i => [$quote, $name, $role, $industrySlug]) {
            Testimonial::updateOrCreate(
                ['client_name' => $name],
                [
                    'quote' => $quote,
                    'role_company' => $role,
                    'industry_id' => Industry::where('slug', $industrySlug)->value('id'),
                    'order' => $i,
                    'is_published' => true,
                ],
            );
        }
    }
}
