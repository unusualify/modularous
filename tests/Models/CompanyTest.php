<?php

namespace Unusualify\Modularous\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\SystemUser\Entities\Company;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Tests\ModelTestCase;

class CompanyTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_company()
    {
        $company = new Company;

        $this->assertEquals(modularousConfig('tables.companies', 'um_companies'), $company->getTable());
    }

    public function test_create_company_with_factory()
    {
        Company::factory(3)->create();
        $this->assertCount(3, Company::all());
    }

    public function test_create_company_without_any_field()
    {
        $company1 = Company::create([]);
        $company2 = Company::create([]);
        $this->assertEquals(1, $company1->id);
        $this->assertEquals(2, $company2->id);
        $this->assertCount(2, Company::all());
    }

    public function test_create_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456',
        ]);

        $this->assertEquals('123 Test St', $company->address);
        $this->assertEquals('Test City', $company->city);
        $this->assertEquals('Test State', $company->state);
        $this->assertEquals('12345', $company->zip_code);
        $this->assertEquals('123-456-7890', $company->phone);
        $this->assertEquals('VAT123456', $company->vat_number);
        $this->assertEquals('TAX123456', $company->tax_id);

    }

    public function test_update_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456',
            'spread_payload' => [
                'is_personal' => true,
            ],
        ]);

        $company->update([
            'name' => 'Updated Company',
            'address' => '456 Updated St',
            'city' => 'Updated City',
            'state' => 'Updated State',
            'zip_code' => '67890',
            'phone' => '987-654-3210',
            'vat_number' => 'VAT654321',
            'tax_id' => 'TAX654321',
        ]);

        $this->assertEquals('Updated Company', $company->name);
        $this->assertEquals('456 Updated St', $company->address);
        $this->assertEquals('Updated City', $company->city);
        $this->assertEquals('Updated State', $company->state);
        $this->assertEquals('67890', $company->zip_code);
        $this->assertEquals('987-654-3210', $company->phone);
        $this->assertEquals('VAT654321', $company->vat_number);
        $this->assertEquals('TAX654321', $company->tax_id);
        $this->assertEquals(true, $company->is_personal);
    }

    public function test_delete_company()
    {
        $company1 = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456',
        ]);

        $company2 = Company::create([
            'name' => 'Test Company 2',
            'address' => '456 Test St',
            'city' => 'Test City 2',
            'state' => 'Test State 2',
            'zip_code' => '67890',
            'phone' => '111-222-3334',
            'vat_number' => 'VAT234567',
            'tax_id' => 'TAX234567',
        ]);

        $this->assertCount(2, Company::all());
        $company2->delete();
        $this->assertFalse(Company::all()->contains('id', $company2->id));
        $this->assertTrue(Company::all()->contains('id', $company1->id));
        $this->assertCount(1, Company::all());

    }

    public function test_company_users()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456',
        ]);
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'company_id' => $company->id,
        ]);

        $this->assertNotNull($user->company);
        $this->assertCount(1, $company->users);
        $this->assertTrue($company->users->contains('id', $user->id));
    }

    public function test_company_company_type()
    {
        $personalCompany = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456',
            'country_id' => 1,
            'spread_payload' => [
                'is_personal' => true,
                'email' => 'test@example.com',
            ],
        ]);

        $this->assertEquals('personal', $personalCompany->company_type);
        $this->assertTrue($personalCompany->is_personal_company);
        $this->assertTrue($personalCompany->isPersonalCompany);
        $this->assertFalse($personalCompany->isCorporateCompany);
        $this->assertTrue($personalCompany->isValid);

        $corporateCompany = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456',
            'country_id' => 1,
            'spread_payload' => [
                'is_personal' => false,
                'email' => 'test@example.com',
            ],
        ]);
        $this->assertEquals('corporate', $corporateCompany->company_type);
        $this->assertFalse($corporateCompany->is_personal_company);
        $this->assertFalse($corporateCompany->isPersonalCompany);
        $this->assertTrue($corporateCompany->isCorporateCompany);
        $this->assertTrue($corporateCompany->isValid);
    }
}
