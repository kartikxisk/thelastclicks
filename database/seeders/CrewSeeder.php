<?php

namespace Database\Seeders;

use App\Models\Crew;
use Illuminate\Database\Seeder;

class CrewSeeder extends Seeder
{
    public function run(): void
    {
        $crew = [
            [
                'slug' => 'aarav-khanna',
                'name' => 'Aarav Khanna',
                'role' => 'Founder · Director of Photography',
                'tagline' => 'Every frame answers a question the brand actually has.',
                'joined' => '2018',
                'discipline' => 'Cinema · Brand',
                'city' => 'Bhopal · IN',
                'bio' => '<p>Aarav started TheLastClicks in 2018 with a single Sony A7 II and a refusal to compromise on craft. Eight years and 547 productions later, he still operates the A camera on every brand shoot worth showing up for.</p><p>He leads the studio\'s color and look-development pipeline and personally grades every premium-brand delivery. His view: authorship doesn\'t survive outsourcing.</p>',
                'skills' => ['ARRI Alexa Mini', 'RED Komodo X', 'Cooke S4', 'DaVinci Resolve', 'Director\'s Eye', 'Color Grade', 'Cinema Lighting', 'Editorial Stills'],
                'credits' => [
                    ['2026', 'Atlas — brand film', 'Director · DOP'],
                    ['2026', 'Udaipur · S & R', 'Lead photo'],
                    ['2025', 'Aurelia GT reveal', 'Director · grade'],
                    ['2025', 'Premium beverage campaign', 'Director'],
                    ['2024', 'Goa · M & A', 'Lead photo'],
                ],
                'photo_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1200&q=85',
                'order' => 0,
            ],
            [
                'slug' => 'maya-iyer',
                'name' => 'Maya Iyer',
                'role' => 'Creative Director',
                'tagline' => 'The brief is a thesis, not a checklist.',
                'joined' => '2020',
                'discipline' => 'Concept · Art',
                'city' => 'Mumbai · IN',
                'bio' => '<p>Maya runs creative direction across brand films and editorial campaigns. She writes most of our treatments and protects the thesis of every project from death-by-revision.</p><p>Background in advertising before joining the studio. She\'s the reason we don\'t pitch in PowerPoint.</p>',
                'skills' => ['Treatment writing', 'Art direction', 'Visual systems', 'Brand strategy', 'Casting', 'Concept development'],
                'credits' => [
                    ['2026', 'Atlas — brand film', 'Creative direction'],
                    ['2025', 'Indé Magazine editorial', 'Art direction'],
                    ['2025', 'Premium beverage campaign', 'Creative lead'],
                    ['2024', 'Quanta keynote recap', 'Treatment + script'],
                ],
                'photo_url' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=1200&q=85',
                'order' => 1,
            ],
            [
                'slug' => 'rohan-bose',
                'name' => 'Rohan Bose',
                'role' => 'Lead Editor & Colorist',
                'tagline' => 'If the cut works on a cheap TV, it works everywhere.',
                'joined' => '2021',
                'discipline' => 'Edit · Grade',
                'city' => 'Bengaluru · IN',
                'bio' => '<p>Rohan leads our edit bay and runs the secondary grade on every premium-brand project. He built our DaVinci pipeline in 2022 and refuses to let anyone outsource a grade.</p><p>Previously an editor at a regional ad agency. Joined to focus exclusively on craft.</p>',
                'skills' => ['DaVinci Resolve', 'Avid Media Composer', 'Adobe Suite', 'ACES', 'Sound design', 'Story structure'],
                'credits' => [
                    ['2026', 'Atlas — brand film', 'Lead editor · color'],
                    ['2025', 'Annual Conference \'25', 'Recap reel'],
                    ['2025', 'Commercial reel \'25', 'Editor'],
                    ['2024', 'Quanta keynote', 'Same-night recap'],
                ],
                'photo_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=1200&q=85',
                'order' => 2,
            ],
            [
                'slug' => 'nisha-rao',
                'name' => 'Nisha Rao',
                'role' => 'Producer',
                'tagline' => 'Logistics is creative work in a different costume.',
                'joined' => '2022',
                'discipline' => 'Logistics · Scale',
                'city' => 'Delhi · IN',
                'bio' => '<p>Nisha runs multi-day productions and the studio\'s relationship with brand-marketing teams. The reason our schedules don\'t slip and our crews get fed.</p><p>Background in event production before crossing into film. Now she runs both — and the producer behind every same-day delivery promise.</p>',
                'skills' => ['Multi-city productions', 'Permits & legal', 'Casting coordination', 'Budget management', 'Client relations', 'Crew scheduling'],
                'credits' => [
                    ['2026', 'Atlas — brand film', 'Lead producer'],
                    ['2025', '12-city automotive launch', 'Field producer'],
                    ['2024', 'Quanta keynote', 'Production lead'],
                ],
                'photo_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=1200&q=85',
                'order' => 3,
            ],
            [
                'slug' => 'devansh-patel',
                'name' => 'Devansh Patel',
                'role' => '2nd Unit DOP',
                'tagline' => 'The shots no one else can get.',
                'joined' => '2023',
                'discipline' => 'Aerials · Action',
                'city' => 'Pune · IN',
                'bio' => '<p>Devansh handles 2nd unit, aerials, and high-speed work. The crew member you call when the shot requires either a drone, a Phantom Flex, or both.</p><p>Licensed drone operator with commercial certifications across India.</p>',
                'skills' => ['DJI Inspire 3', 'Phantom Flex 4K', 'Movi Pro', 'High-speed cinematography', 'Drone licensing', 'Aerial coverage'],
                'credits' => [
                    ['2025', 'Aurelia GT reveal', 'High-speed unit'],
                    ['2025', '12-city automotive launch', '2nd unit DOP'],
                    ['2026', 'Udaipur · S & R', 'Drone unit'],
                ],
                'photo_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=1200&q=85',
                'order' => 4,
            ],
            [
                'slug' => 'anaya-singh',
                'name' => 'Anaya Singh',
                'role' => 'Photographer',
                'tagline' => 'Stills are their own discipline, not an afterthought.',
                'joined' => '2023',
                'discipline' => 'Editorial · Fashion',
                'city' => 'Mumbai · IN',
                'bio' => '<p>Anaya leads our editorial and fashion photography. She runs the still-image side of every brand campaign and shoots most of our magazine work.</p><p>Hasselblad-trained, film-tone obsessed. Minimal retouch, maximum frame.</p>',
                'skills' => ['Hasselblad H6D', 'Profoto strobes', 'Capture One', 'Film-tone retouch', 'Editorial portraits', 'Fashion direction'],
                'credits' => [
                    ['2025', 'Indé Magazine editorial', 'Lead photographer'],
                    ['2025', 'Premium beverage stills', 'Tabletop + portraits'],
                    ['2024', 'Lookbook series (4 brands)', 'Lead photographer'],
                ],
                'photo_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=1200&q=85',
                'order' => 5,
            ],
        ];

        foreach ($crew as $row) {
            Crew::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
