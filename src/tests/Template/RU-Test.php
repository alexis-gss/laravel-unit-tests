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

    /** @return void */
    public function testGuestCannotAccess{{ class }}sUpdateView(): void
    {
        $model    = {{ class }}::factory()->createOneQuietly();
        $response = $this->get(route(
            config('unit-tests.route.prefix') .
                '{{ snakeCaseClass }}s.' .
                config('unit-tests.view.resources-update'),
            $model->getKey()
        ));
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /**
     * TESTS GUEST CANNOT POST MODEL.
     */

    /** @return void */
    public function testGuestCannotUpdate{{ class }}(): void
    {
        $model    = {{ class }}::factory()->createOneQuietly();
        $response = $this->patch(
            route(
                config('unit-tests.route.prefix') . '{{ snakeCaseClass }}s.' . config('unit-tests.route.action-update'),
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

    /** @return void */
    public function testUserConceptorCanAccess{{ class }}sUpdateView(): void
    {
        $authModel = AuthModel::factory()->createOneQuietly();
        $authModel->update(['role' => RoleEnum::conceptor]);
        $model    = {{ class }}::factory()->createOneQuietly();
        $response = $this->actingAs($authModel, 'backend')->get(
            route(config('unit-tests.route.prefix') . '{{ snakeCaseClass }}s.' . config('unit-tests.view.resources-update'), $model)
        );
        $response->assertSuccessful();
        $response->assertViewIs(
            config('unit-tests.view.prefix') .
                'pages.{{ snakeCaseClass }}s.' .
                config('unit-tests.view.resources-update')
        );
    }

    /**
     * TESTS CAN ACTIONS ON MODEL.
     */

    /** @return void */
    public function testCanUpdate{{ class }}(): void
    {
        $model = {{ class }}::factory()->createOneQuietly();

        $fieldTest = "";
        foreach (config('unit-tests.list-fields') as $field) {
            if (Schema::hasColumn($model->getTable(), $field)) {
                $model->update([$field => "test"]);
                $fieldTest = $field;
                break;
            }
        }

        $this->assertTrue($model->wasChanged());
        $this->assertTrue(array_key_exists($fieldTest, $model->getChanges()));
        $this->assertModelExists($model);
    }
}
