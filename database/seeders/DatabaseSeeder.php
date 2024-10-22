<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Role_Permission;
use App\Models\User;
use App\Models\Consultant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $admin_role = Role::create(['name' => 'owner', 'translate' => 'مدیر', 'active' => true, 'description' => 'مدیر']);
        User::create([
            'username' => 'mhdehghanzadeh',
            'email' => 'mhdehghanzadeh@yahoo.com',
            'phone' => '09981284843',
            'password' => 'Hajiazz021+8',
            'active' => true,
            'role_id' => $admin_role->id,
            'email_verified_at' => date("Y-m-d h:i:s"),
        ]);
        User::create([
            'username' => 'mahmadi',
            'email' => 'mahdiahmadibusy1400@gmail.com',
            'phone' => '09102413207',
            'password' => 'Hajiazz021+8',
            'active' => true,
            'role_id' => $admin_role->id,
            'email_verified_at' => date("Y-m-d h:i:s"),
        ]);

        //create roleس
        Role::create(['name' => 'contact', 'translate' => 'بیمار', 'active' => true, 'description' => 'بیمار']);
        Role::create(['name' => 'support', 'translate' => 'پشتیان', 'active' => true, 'description' => 'پشتیان']);
        $consultant_role = Role::create(['name' => 'consultant', 'translate' => 'پزشک', 'active' => true, 'description' => 'پزشک']);
    
        //create consultant account
        //create consultant
        /*   Consultant::create([
            'user_id' => $consultant_user->id,
            'name' => 'دکتر سید محسن خوش نیت نیکو',
            'specialty' => 'فوق تخصص غدد درون ریز و متابولیسم',
            'npcode' => '37205',
            'adress' => 'خ سهروردی شمالی - کوی نقدی - پلاک 34 - ساختمان پزشکان - طبقه پنجم',
            'location' => '35.734521617056416, 51.44164844232044',
            'telephone' => '021-88760606',
            'price' => 100,
            'active' => true,
        ]); */

        //create consultant permissions
        $consultant_permissions = [
            ['name' => 'counselings.show', 'translate' => 'مشاهده ویزیت', 'description' => 'مشاهده ویزیت'],
            ['name' => 'counselings.edit', 'translate' => 'ویرایش ویزیت', 'description' => 'ویرایش ویزیت'],
            ['name' => 'contacts.show', 'translate' => 'مشاهده بیمار', 'description' => 'مشاهده بیمار'],
            ['name' => 'contacts.edit', 'translate' => 'ویرایش بیمار', 'description' => 'ویرایش بیمار'],
            ['name' => 'consultants.show', 'translate' => 'مشاهده پزشک', 'description' => 'مشاهده پزشک'],
            ['name' => 'consultants.edit', 'translate' => 'ویرایش پزشک', 'description' => 'ویرایش پزشک'],
        ];
        

        foreach($consultant_permissions as $item){
            $permission = Permission::create($item);
            Role_Permission::create([
                'role_id' => $consultant_role->id,
                'permission_id' => $permission->id,
            ]);
        }

    }
}
