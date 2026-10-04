<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contactus\StoreRequest;
use App\Http\Resources\Branch\AllResource as BranchAllResource;
use App\Http\Resources\Faq\AllResource;
use App\Http\Resources\ServiceCenter\AllResource as ServiceCenterAllResource;
use App\Models\Branch;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\Page;
use App\Models\ServiceCenter;
use App\Models\Setting;
use App\Models\Tire;
use Illuminate\Http\Request;

class HelpCenterController extends Controller
{
    public function faqs(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:orders,delivery,payments,account,stores&drivers,other',
            // 'type'   => 'required|string',
            'search' => 'nullable|string'
        ]);

        $query = Faq::where('type', $request->type);

        if ($request->filled('search')) {

            $locale = app()->getLocale();

            $query->where(function ($q) use ($request, $locale) {
                $q->where("question->$locale", 'like', '%' . $request->search . '%')
                    ->orWhere("answer->$locale", 'like', '%' . $request->search . '%');
            });
        }

        $faqs = $query->get();
        $types = ['orders', 'delivery', 'payments', 'account', 'stores&drivers', 'other'];

        return $this->sendResponse(
            data: [
                'types' => $types,
                'faqs' =>
                AllResource::collection(
                    $faqs
                ),
            ]

        );
    }


    // public function settings()
    // {
    //     return $this->sendResponse(data: Setting::pluck('value', 'key')->toArray());
    // }

    public function settings()
    {
        $settings = Setting::all()->keyBy('key');
        $locale = app()->getLocale();

        return $this->sendResponse(data: [
            'welcome' => [
                'image' => isset($settings['welcome_image'])
                    ? [asset('storage/' . $settings['welcome_image']->value)]
                    : null,
                'text'  => isset($settings['welcome_text'])
                    ? ($settings['welcome_text']->value[$locale]
                        ?? $settings['welcome_text']->value['en']
                        ?? null)
                    : null,
            ],
            'login' => [
                'image' => isset($settings['login_image'])
                    ? asset('storage/' . $settings['login_image']->value)
                    : null,
                'link'  => $settings['login_link']->value ?? null,
            ],
            'quick_action' => [
                'image' => isset($settings['quick_action_image'])
                    ? asset('storage/' . $settings['quick_action_image']->value)
                    : null,
            ],
            'quick_order' => $this->formatQuickOrderSettings($settings, $locale),
            'contact' => [
                'phone' => $settings['phone']->value ?? null,
                'whatsapp' => $settings['whts']->value ?? null,
                'email' => $settings['email']->value ?? null,
                'instagram' => $settings['instagram']->value ?? null,
                'facebook' => $settings['facebook']->value ?? null,
            ],
            'color' => [
                'main_color' => isset($settings['main_color'])
                    ? $settings['main_color']->value
                    : '#c720a4',
                'text_color' => isset($settings['text_color'])
                    ? $settings['text_color']->value
                    : '#1F2937',
                'second_color' => isset($settings['second_color'])
                    ? $settings['second_color']->value
                    : '#ff1493',
            ],
            'dark_color' => [
                'main_color' => isset($settings['dark_main_color'])
                    ? $settings['dark_main_color']->value
                    : '#1a0a16',
                'text_color' => isset($settings['dark_text_color'])
                    ? $settings['dark_text_color']->value
                    : '#fce7f3',
                'second_color' => isset($settings['dark_second_color'])
                    ? $settings['dark_second_color']->value
                    : '#a020f0',
            ],
        ]);
    }

    /**
     * «طلب سريع» section: visibility + placement pages + background + step cards.
     *
     * @param  \Illuminate\Support\Collection<string, Setting>  $settings
     * @return array<string, mixed>
     */
    private function formatQuickOrderSettings($settings, string $locale): array
    {
        // Section visibility (page body). Independent from the header button.
        $sectionRaw = $settings['quick_order_enabled']->value ?? true;
        $showSection = filter_var($sectionRaw, FILTER_VALIDATE_BOOLEAN);

        // Header / nav button — separate admin switch.
        $headerRaw = $settings['quick_order_header_enabled']?->value ?? true;
        $showHeader = filter_var($headerRaw, FILTER_VALIDATE_BOOLEAN);

        $bgImage = $settings['quick_order_background_image']->value ?? null;
        if (is_array($bgImage)) {
            $bgImage = $bgImage[0] ?? null;
        }

        $cardVariant = $settings['quick_order_card_variant']->value ?? 'horizontal';
        if (! in_array($cardVariant, ['horizontal', 'vertical', 'square'], true)) {
            $cardVariant = 'horizontal';
        }

        $steps = $settings['quick_order_steps']->value ?? [];
        if (! is_array($steps)) {
            $steps = [];
        }

        [$pageIds, $pageSlugs] = $this->resolveQuickOrderPages(
            $settings['quick_order_page_ids']?->value
        );

        return [
            // BC: is_enabled = section only (was previously master for header+section).
            'is_enabled' => $showSection,
            'show_header' => $showHeader,
            'show_section' => $showSection,
            'page_ids' => $pageIds,
            'page_slugs' => $pageSlugs,
            'background_image' => $bgImage
                ? asset('storage/' . ltrim((string) $bgImage, '/'))
                : null,
            'background_color' => $settings['quick_order_background_color']->value ?? '#FFE8D6',
            'card_background_color' => $settings['quick_order_card_background_color']->value ?? '#FFFFFF',
            'card_variant' => $cardVariant,
            'badge' => $this->localizedSettingText($settings['quick_order_badge']->value ?? null, $locale),
            'title' => $this->localizedSettingText($settings['quick_order_title']->value ?? null, $locale),
            'subtitle' => $this->localizedSettingText($settings['quick_order_subtitle']->value ?? null, $locale),
            'cta' => $this->localizedSettingText($settings['quick_order_cta']->value ?? null, $locale),
            'steps' => collect($steps)->map(function ($step) use ($locale) {
                return [
                    'number' => (int) ($step['number'] ?? 0),
                    'icon' => $step['icon'] ?? null,
                    'title' => $this->localizedSettingText($step['title'] ?? null, $locale),
                    'description' => $this->localizedSettingText($step['description'] ?? null, $locale),
                ];
            })->values()->all(),
            'action' => [
                'page_slug' => 'custom_order_request',
                'route' => '/api/user/custom-order-requests',
            ],
        ];
    }

    /**
     * Normalize stored page IDs and resolve slugs for clients.
     * Missing setting → home only (backward compatible).
     * Explicit empty array → no pages (section hidden; header still follows is_enabled).
     *
     * @return array{0: list<int>, 1: list<string>}
     */
    private function resolveQuickOrderPages(mixed $raw): array
    {
        if ($raw === null) {
            return $this->homeQuickOrderPages();
        }

        if (! is_array($raw)) {
            return $this->homeQuickOrderPages();
        }

        $ids = collect($raw)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [[], []];
        }

        $pages = Page::query()
            ->whereIn('id', $ids)
            ->get(['id', 'slug'])
            ->keyBy('id');

        $orderedIds = [];
        $slugs = [];
        foreach ($ids as $id) {
            $page = $pages->get($id);
            if (! $page) {
                continue;
            }
            $orderedIds[] = (int) $page->id;
            $slugs[] = (string) $page->slug;
        }

        return [$orderedIds, $slugs];
    }

    /**
     * @return array{0: list<int>, 1: list<string>}
     */
    private function homeQuickOrderPages(): array
    {
        $home = Page::query()->where('slug', 'home')->first(['id', 'slug']);
        if ($home) {
            return [[(int) $home->id], ['home']];
        }

        return [[], ['home']];
    }

    private function localizedSettingText(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        return $value[$locale] ?? $value['en'] ?? $value['ar'] ?? null;
    }

    // public function contactus(StoreRequest $request)
    // {
    //     Contact::create($request->validated());
    //     return $this->sendResponse();
    // }
}
