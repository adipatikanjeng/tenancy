<?php
namespace App\Console\Commands;
use App\User;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
class CreateTenant extends Command
{
    protected $signature = 'tenant:create {name} {email} {fqdn}';
    protected $description = 'Creates a tenant with the provided name and email address e.g. php artisan tenant:create boise boise@example.com';
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $fqdn = $this->argument('fqdn');
        if ($this->tenantExists($name, $email)) {
            $this->error("A tenant with name '{$name}' and/or '{$email}' already exists.");
            return;
        }
        $hostname = $this->registerTenant($fqdn, $email);
        app(Environment::class)->hostname($hostname);
        // we'll create a random secure password for our to-be admin
        $password = str_random();
        $this->addAdmin($name, $email, $password);
        $this->info("Tenant '{$name}' is created and is now accessible at {$hostname->fqdn}");
        $this->info("Admin {$email} can log in using password {$password}");
    }
    private function tenantExists($name, $email)
    {
        return User::where('name', $name)->orWhere('email', $email)->exists();
    }
    private function registerTenant($fqdn, $email)
    {
        $website = new Website;
        $website->uuid = str_random(10);
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
