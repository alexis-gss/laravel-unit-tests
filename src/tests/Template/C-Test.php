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
    public function testGuestCannotAccess{{ class }}sCreateView(): void
    {
        $response = $this->get(route(
            config('unit-tests.route.prefix') .
                '{{ snakeCaseClass }}s.' .
                config('unit-tests.view.resources-create')
        ));
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /**
     * TESTS GUEST CANNOT POST MODEL.
     */

    /** @return void */
    public function testGuestCannotCreate{{ class }}(): void
    {
        $model    = {{ class }}::factory()->createOneQuietly();
        $response = $this->post(
            route(
                config('unit-tests.route.prefix') . '{{ snakeCaseClass }}s.' . config('unit-tests.route.action-create'),
                $model->getKey()
            ),
            $model->toArray()
        );
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /**
     * TESTS USER CONCEPTOR ACCESS VIEWS.
     */

    /** @return void */
    public function testUserConceptorCanAccess{{ class }}sCreateView(): void
    {
        $authModel = AuthModel::factory()->createOneQuietly();
        $authModel->update(['role' => RoleEnum::conceptor]);
        $response = $this->actingAs($authModel, 'backend')->get(
            route(config('unit-tests.route.prefix') . '{{ snakeCaseClass }}s.' . config('unit-tests.view.resources-create'))
        );
        $response->assertSuccessful();
        $response->assertViewIs(
            config('unit-tests.view.prefix') .
                'pages.{{ snakeCaseClass }}s.' .
                config('unit-tests.view.resources-create')
        );
    }

    /**
     * TESTS CAN ACTIONS ON MODEL.
     */

    /** @return void */
    public function testCanCreate{{ class }}(): void
    {
        $model = {{ class }}::factory()->createOneQuietly();
        $this->assertModelExists($model);
    }
}
