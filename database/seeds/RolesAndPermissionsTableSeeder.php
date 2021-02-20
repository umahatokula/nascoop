<?php

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // CREATE AND ATTACH PERMISSIONS
        $create_member = Permission::create(['name' => 'create member']);
        $read_member = Permission::create(['name' => 'read member']);
        $update_member = Permission::create(['name' => 'update member']);
        $disable_member = Permission::create(['name' => 'disable member']);
        $view_member_dashboard = Permission::create(['name' => 'view member dashboard']);
        $add_to_savings = Permission::create(['name' => 'add to savings']);
        $withdraw_from_savings = Permission::create(['name' => 'withdraw from savings']);
        $change_monthly_contribution = Permission::create(['name' => 'change monthly contribution']);
        $view_member_ledger = Permission::create(['name' => 'view member ledger']);

        $create_centre = Permission::create(['name' => 'create centre']);
        $read_centre = Permission::create(['name' => 'read centre']);
        $update_centre = Permission::create(['name' => 'update centre']);
        $delete_centre = Permission::create(['name' => 'delete centre']);

        $create_bank = Permission::create(['name' => 'create bank']);
        $read_bank = Permission::create(['name' => 'read bank']);
        $update_bank = Permission::create(['name' => 'update bank']);
        $delete_bank = Permission::create(['name' => 'delete bank']);

        $ltl_create = Permission::create(['name' => 'create long term loan']);
        $ltl_repay = Permission::create(['name' => 'long term loan repayment']);

        $stl_create = Permission::create(['name' => 'create short term loan']);
        $stl_repay = Permission::create(['name' => 'short term loan repayment']);

        $com_create = Permission::create(['name' => 'create commodity loan']);
        $com_repay = Permission::create(['name' => 'commodity loan repayment']);

        $start_processing_pending_trxn = Permission::create(['name' => 'start processing pending trxn']);
        $authorize_pending_trxn = Permission::create(['name' => 'authorize pending trxn']);


        $generate_IPPIS_deduction_file = Permission::create(['name' => 'generate IPPIS deduction file']);
        $import_IPPIS_deduction_file = Permission::create(['name' => 'import and reconcile IPPIS deduction file']);

        $generate_reports = Permission::create(['name' => 'generate reports']);

        $manage_users = Permission::create(['name' => 'manage users']);

        $post_ippis_repayment = Permission::create(['name' => 'post ippis repayment']);
        $post_3rd_party_payments = Permission::create(['name' => 'post 3rd party payments']);
        $create_chart_of_account = Permission::create(['name' => 'create chart of account']);
        $direct_coa_entry = Permission::create(['name' => 'direct coa entry']);



        // CREATE ROLES
        $member = Role::create(['name' => 'member']);
        $secretary = Role::create(['name' => 'secretary']);
        $treasurer = Role::create(['name' => 'treasurer']);
        $accountant = Role::create(['name' => 'accountant']);
        $fin_sec = Role::create(['name' => 'financial secretary']);
        $president = Role::create(['name' => 'president']);
        $coop_staff = Role::create(['name' => 'cooperative staff']);
        $auditor = Role::create(['name' => 'auditor']);        
        $manager = Role::create(['name' => 'manager']);        
        $super_admin = Role::create(['name' => 'super-admin']);


        // ASSIGN PERMISSIONS TO ROLES
        $member->givePermissionTo([$view_member_ledger]);

        $secretary->givePermissionTo([$create_member, $read_member, $update_member, $disable_member, $view_member_dashboard, $view_member_ledger, $create_centre, $read_centre, $update_centre, $delete_centre, $read_bank, $update_bank, $generate_reports]);

        $treasurer->givePermissionTo([$view_member_dashboard, $view_member_ledger, $read_centre, $update_centre, $create_bank,$read_bank, $update_bank, $delete_bank, $generate_reports, $generate_IPPIS_deduction_file, $import_IPPIS_deduction_file, $post_ippis_repayment, $post_3rd_party_payments, $create_chart_of_account, $direct_coa_entry]);

        $accountant->givePermissionTo([$read_member, $update_member, $view_member_dashboard, $add_to_savings, $withdraw_from_savings, $change_monthly_contribution, $view_member_ledger, $read_centre, $ltl_create, $ltl_repay, $stl_create, $stl_repay, $com_create, $com_repay, $generate_IPPIS_deduction_file, $import_IPPIS_deduction_file, $generate_reports, $post_3rd_party_payments, $direct_coa_entry, $start_processing_pending_trxn]);

        $fin_sec->givePermissionTo([$create_member, $read_member, $view_member_dashboard, $add_to_savings, $withdraw_from_savings, $change_monthly_contribution, $view_member_ledger, $create_centre, $read_centre, $update_centre, $ltl_create, $ltl_repay, $stl_create, $stl_repay, $com_create, $com_repay, $generate_IPPIS_deduction_file, $import_IPPIS_deduction_file, $authorize_pending_trxn]);

        $president->givePermissionTo([$read_member, $view_member_dashboard, $view_member_ledger, $read_centre, $generate_reports]);

        $coop_staff->givePermissionTo([$read_member, $update_member, $view_member_dashboard, $add_to_savings, $withdraw_from_savings, $change_monthly_contribution, $view_member_ledger, $read_centre, $ltl_create, $ltl_repay, $stl_create, $stl_repay, $com_create, $com_repay, $start_processing_pending_trxn]);

        $auditor->givePermissionTo([$read_member, $view_member_dashboard, $view_member_ledger, $read_centre, $generate_reports]);

        $manager->givePermissionTo([$create_member, $read_member, $update_member, $disable_member, $view_member_dashboard, $read_centre,$read_bank, $generate_reports, $add_to_savings, $withdraw_from_savings, $change_monthly_contribution, $view_member_ledger, $read_centre, $ltl_create, $ltl_repay, $stl_create, $stl_repay, $com_create, $com_repay, $start_processing_pending_trxn]);

        // $super_admin->givePermissionTo([$create_member, $read_member, $update_member, $disable_member, $view_member_dashboard, $add_to_savings, $withdraw_from_savings, $change_monthly_contribution, $view_member_ledger, $create_centre, $read_centre, $update_centre,  $delete_centre, $create_bank, $read_bank, $update_bank, $delete_bank,  $ltl_create, $ltl_repay, $stl_create, $stl_repay, $com_create, $com_repay, $generate_IPPIS_deduction_file, $import_IPPIS_deduction_file, $generate_reports]);

    }
}
