<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        if ($organisations->isEmpty()) {
            return;
        }

        $channels = [
            ['name' => 'Modern Trade', 'node_level' => 0],
            ['name' => 'General Trade', 'node_level' => 0],
            ['name' => 'E-Commerce', 'node_level' => 0],
            ['name' => 'HoReCa', 'node_level' => 0],
            ['name' => 'Institutional', 'node_level' => 0],
        ];

        foreach ($organisations as $org) {
            foreach ($channels as $channel) {
                $parent = Channel::create(array_merge($channel, [
                    'uuid' => fake()->uuid(),
                    'organisation_id' => $org->id,
                    'parent_id' => null,
                    'status' => true,
                ]));

                $subChannels = $this->getSubChannels($channel['name']);
                foreach ($subChannels as $sub) {
                    Channel::create(array_merge($sub, [
                        'uuid' => fake()->uuid(),
                        'organisation_id' => $org->id,
                        'parent_id' => $parent->id,
                        'node_level' => 1,
                        'status' => true,
                    ]));
                }
            }
        }
    }

    private function getSubChannels(string $parentName): array
    {
        return match ($parentName) {
            'Modern Trade' => [
                ['name' => 'Hypermarkets'],
                ['name' => 'Supermarkets'],
                ['name' => 'Convenience Stores'],
            ],
            'General Trade' => [
                ['name' => 'Traditional Retail'],
                ['name' => 'Kirana Stores'],
                ['name' => 'Wholesale'],
            ],
            'E-Commerce' => [
                ['name' => 'Marketplace'],
                ['name' => 'D2C'],
                ['name' => 'Quick Commerce'],
            ],
            'HoReCa' => [
                ['name' => 'Hotels'],
                ['name' => 'Restaurants'],
                ['name' => 'Cafes'],
            ],
            'Institutional' => [
                ['name' => 'Corporate'],
                ['name' => 'Government'],
                ['name' => 'Educational'],
            ],
            default => [],
        };
    }
}