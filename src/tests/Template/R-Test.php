<?php

namespace {{ namespace }};

use App\Enums\Users\RoleEnum;
use App\Models\{{ class }};
use App\Models\User as AuthModel;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class {{ class }}Test extends TestCase
{
    /**
     * TESTS GUEST CANNOT ACCESS VIEWS.
     */

    /** @return void */
    public function testGuestCannotAccess{{ class }}sReadView(): void
    {
        $model    = {{ class }}::factory()->createOneQuietly();
        $response = $this->get(route(
            config('unit-tests.route.prefix') .
                '{{ snakeCaseClass }}s.' .
                config('unit-tests.view.resources-read'),
            $model->getKey()
        ));
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /**
     * TESTS USER CONCEPTOR ACCESS VIEWS.
     */

    /** @return void */
    public function testUserConceptorCanAccess{{ class }}sReadView(): void
    {
        $authModel = AuthModel::factory()->createOneQuietly();
        $authModel->update(['role' => RoleEnum::conceptor]);
        $model    = {{ class }}::factory()->createOneQuietly();
        $response = $this->actingAs($authModel, 'backend')->get(
            route(config('unit-tests.route.prefix') . '{{ snakeCaseClass }}s.' . config('unit-tests.view.resources-read'), $model)
        );
        $response->assertSuccessful();
        $response->assertViewIs(
            config('unit-tests.view.prefix') .
                'pages.{{ snakeCaseClass }}s.' .
                config('unit-tests.view.resources-read')
        );
    }
}
