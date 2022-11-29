<?php
namespace Database\Seeders;

use App\Models\User as ModelsUser;
use Illuminate\Database\Seeder;
use App\User;
class DummyUsersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $userData = [
            [
               'name'=>'Admin',
               'email'=>'admin@gmail.com',
                'type'=>'admin',
               'password'=> bcrypt('admin@123'),
            ],
            [
               'name'=>'Regular User',
               'email'=>'normal@gmail.com',
                'type'=>'normal',
               'password'=> bcrypt('normal@123'),
            ],
        ];

        foreach ($userData as $key => $val) {
            ModelsUser::create($val);
        }
    }

}
