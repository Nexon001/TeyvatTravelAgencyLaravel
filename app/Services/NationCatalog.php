<?php

namespace App\Services;

/**
 * Read-only access to the nations and itineraries in resources/data/nations.php.
 * When that file moves to the database, only this class needs to change.
 */
class NationCatalog
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $nations = null;

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return $this->nations ??= include resource_path('data/nations.php');
    }

    /** @return list<string> */
    public function slugs(): array
    {
        return array_keys($this->all());
    }

    public function name(string $slug): ?string
    {
        return $this->all()[$slug]['name'] ?? null;
    }

    /**
     * Resolve an itinerary id such as "liyue-1" (or "liyue-featured", which the home page uses
     * for each nation's first itinerary).
     *
     * @return array{id: string, nation: string, nation_name: string, title: string, days: int}|null
     */
    public function itinerary(string $id): ?array
    {
        if (! preg_match('/^([a-z]+)-(\d+|featured)$/', $id, $match)) {
            return null;
        }

        $nation = $this->all()[$match[1]] ?? null;
        $index = $match[2] === 'featured' ? 0 : (int) $match[2];
        $itinerary = $nation['itineraries'][$index] ?? null;

        if ($itinerary === null) {
            return null;
        }

        return [
            'id' => $match[1].'-'.$index,
            'nation' => $match[1],
            'nation_name' => $nation['name'],
            'title' => $itinerary['title'],
            'days' => (int) $itinerary['days'],
        ];
    }
}
