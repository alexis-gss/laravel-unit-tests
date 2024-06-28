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
     * TESTS GUEST CANNOT POST MODEL.
     */

    /** @return void */
    public function testGuestCannotDelete{{ class }}(): void
    {
        $model    = {{ class }}::factory()->createOneQuietly();
        $response = $this->delete(
            route(
                config('unit-tests.route.prefix') . '{{ snakeCaseClass }}s.' . config('unit-tests.route.action-delete'),
                $model->getKey()
            ),
            $model->toArray()
        );
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /**
     * TESTS CAN ACTIONS ON MODEL.
     */

    /** @return void */
    public function testCanDelete{{ class }}(): void
    {
        $model = {{ class }}::factory()->createOneQuietly();
        $model->delete();
        $this->assertModelMissing($model);
    }
}
