<?php

namespace App\Console\Commands;

use App\User;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;;

class CreateTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create multiple tenats from existing data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tenants = [
            ['domain' => 'foo.multi-tenancy.non', 'name' => 'Foo Customer1', 'email' => 'customer1@foo.non'],
            ['domain' => 'bar.multi-tenancy.non', 'name' => 'Bar Customer1', 'email' => 'customer1@bar.non'],
            ['domain' => 'baz.multi-tenancy.non', 'name' => 'Baz Customer1', 'email' => 'customer@baz.non'],
        ];
        foreach ($tenants as $tenant) {
            $name = $tenant['name'];
            $email = $tenant['email'];
            $domain = $tenant['domain'];
            if ($this->tenantExists($name, $email)) {
                $this->error("A tenant with name '{$name}' and/or '{$email}' already exists.");
                return;
            }
            $hostname = $this->registerTenant($name, $domain, $email);
            app(Environment::class)->hostname($hostname);
            // we'll create a random secure password for our to-be admin
            $password = str_random();
            $this->addAdmin($name, $email, $password);
            $this->info("Tenant '{$name}' is created and is now accessible at {$hostname->fqdn}");
            $this->info("Admin {$email} can log in using password {$password}");
        }

    }
    private function tenantExists($name, $email)
    {
        return User::where('name', $name)->orWhere('email', $email)->exists();
    }
    private function registerTenant($name, $fqdn, $email)
    {
        $name = str_replace(" ", "_", $name);
        $website = new Website;
        $website->uuid = strtolower($name.'_'.str_random(3));
        app(WebsiteRepository::class)->create($website);
        app(Environment::class)->tenant($website);
        // associate the website with a hostname
        $hostname = new Hostname;
        $hostname->fqdn = "{$fqdn}";
        app(HostnameRepository::class)->attach($hostname, $website);
        return $hostname;
    }
    private function addAdmin($name, $email, $password)
    {
        $admin = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        return $admin;
    }
}
