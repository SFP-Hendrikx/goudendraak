<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfferVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_can_create_offer_for_one_or_more_menu_items(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        $menuItem = $this->createMenuItem([
            'naam' => 'Babi Pangang',
            'price' => 10.00,
        ]);

        $response = $this
            ->withSession([
                'kassa_logged_in' => true,
                'kassa_is_admin' => true,
            ])
            ->post(route('kassa.aanbiedingen.store'), [
                'titel' => 'Weekaanbieding',
                'beschrijving' => 'Alleen deze week goedkoper.',
                'startdatum' => '2026-06-15',
                'einddatum' => '2026-06-21',
                'is_actief' => '1',
                'menu_items' => [
                    $menuItem->id => [
                        'selected' => '1',
                        'actieprijs' => '8,50',
                    ],
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kassa.aanbiedingen'));

        $this->assertDatabaseHas('aanbiedingen', [
            'titel' => 'Weekaanbieding',
            'startdatum' => '2026-06-15',
            'einddatum' => '2026-06-21',
            'is_actief' => true,
        ]);

        $this->assertDatabaseHas('aanbieding_menu', [
            'menu_item_id' => $menuItem->id,
            'actieprijs' => 8.50,
        ]);
    }

    public function test_current_offer_is_visible_on_offers_page_and_menu_page(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        $menuItem = $this->createMenuItem([
            'menunummer' => 12,
            'naam' => 'Babi Pangang',
            'price' => 10.00,
            'soortgerecht' => 'Vleesgerechten',
        ]);

        $offer = Offer::query()->create([
            'titel' => 'Weekaanbieding',
            'beschrijving' => 'Alleen deze week goedkoper.',
            'startdatum' => '2026-06-15',
            'einddatum' => '2026-06-21',
            'is_actief' => true,
        ]);

        $offer->menuItems()->attach($menuItem->id, [
            'actieprijs' => 8.50,
        ]);

        $this->get(route('offers'))
            ->assertOk()
            ->assertSee('Weekaanbieding')
            ->assertSee('Babi Pangang')
            ->assertSee('Normaal € 10,00')
            ->assertSee('Nu € 8,50');

        $this->get(route('menu'))
            ->assertOk()
            ->assertSee('Vleesgerechten')
            ->assertSee('Weekaanbieding')
            ->assertSee('Babi Pangang')
            ->assertSee('Normaal € 10,00')
            ->assertSee('Nu € 8,50');
    }

    public function test_expired_offer_is_not_visible_on_public_pages(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        $menuItem = $this->createMenuItem([
            'naam' => 'Foe Yong Hai',
            'price' => 9.00,
        ]);

        $offer = Offer::query()->create([
            'titel' => 'Oude aanbieding',
            'beschrijving' => null,
            'startdatum' => '2026-06-01',
            'einddatum' => '2026-06-07',
            'is_actief' => true,
        ]);

        $offer->menuItems()->attach($menuItem->id, [
            'actieprijs' => 7.50,
        ]);

        $this->get(route('offers'))
            ->assertOk()
            ->assertDontSee('Oude aanbieding')
            ->assertDontSee('Nu € 7,50');

        $this->get(route('menu'))
            ->assertOk()
            ->assertSee('Foe Yong Hai')
            ->assertSee('€ 9,00')
            ->assertDontSee('Oude aanbieding')
            ->assertDontSee('Nu € 7,50');
    }

    public function test_offer_price_must_be_lower_than_normal_price(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        $menuItem = $this->createMenuItem([
            'naam' => 'Tjap Tjoy',
            'price' => 10.00,
        ]);

        $response = $this
            ->withSession([
                'kassa_logged_in' => true,
                'kassa_is_admin' => true,
            ])
            ->from(route('kassa.aanbiedingen.create'))
            ->post(route('kassa.aanbiedingen.store'), [
                'titel' => 'Foute aanbieding',
                'startdatum' => '2026-06-15',
                'einddatum' => '2026-06-21',
                'is_actief' => '1',
                'menu_items' => [
                    $menuItem->id => [
                        'selected' => '1',
                        'actieprijs' => '12,00',
                    ],
                ],
            ]);

        $response
            ->assertRedirect(route('kassa.aanbiedingen.create'))
            ->assertSessionHasErrors("menu_items.{$menuItem->id}.actieprijs");

        $this->assertSame(0, DB::table('aanbiedingen')->count());
    }

    private function createMenuItem(array $overrides = []): MenuItem
    {
        return MenuItem::query()->create(array_merge([
            'menunummer' => 1,
            'menu_toevoeging' => null,
            'naam' => 'Testgerecht',
            'price' => 10.00,
            'soortgerecht' => 'Testcategorie',
            'beschrijving' => 'Testomschrijving',
            'is_actief' => true,
        ], $overrides));
    }
}
