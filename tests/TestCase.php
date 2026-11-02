<?php

namespace TCG\Voyager\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Orchestra\Testbench\BrowserKit\TestCase as OrchestraTestCase;
use TCG\Voyager\Models\User;
use TCG\Voyager\VoyagerServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected $withDummy = true;

    public function setUp(): void
    {
        parent::setUp();

        // Persistent databases keep tables between tests, unlike sqlite :memory:
        if (env('DB_CONNECTION', 'sqlite') !== 'sqlite') {
            $this->artisan('db:wipe');
        }

        $this->loadLaravelMigrations();

        if (!is_dir(base_path('routes'))) {
            mkdir(base_path('routes'));
        }

        if (!file_exists(base_path('routes/web.php'))) {
            file_put_contents(
                base_path('routes/web.php'),
                "<?php Route::get('/', function () {return view('welcome');});"
            );
        }

        // Orchestra Testbench does not contain this file and can't create autoload without
        if (!is_dir(base_path('tests/'))) {
            mkdir(base_path('tests/'));

            file_put_contents(
                base_path('tests/TestCase.php'),
                "<?php\n\n"
            );
        }

        $this->app->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware('Illuminate\Session\Middleware\StartSession');
        $this->app->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware('Illuminate\View\Middleware\ShareErrorsFromSession');

        $this->install();
    }

    protected function getPackageProviders($app)
    {
        return [
            VoyagerServiceProvider::class,
        ];
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory: unless DB_CONNECTION is set (mysql/pgsql in CI)
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', env('DB_CONNECTION', 'sqlite') === 'sqlite' ? [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ] : array_merge($app['config']->get('database.connections.'.env('DB_CONNECTION')), [
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT'),
            'database' => env('DB_DATABASE', 'voyager'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ]));

        // Setup Voyager configuration
        $app['config']->set('voyager.user.namespace', User::class);

        // Setup Authentication configuration
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function install()
    {
        $this->artisan('voyager:install', ['--with-dummy' => $this->withDummy]);

        app(VoyagerServiceProvider::class, ['app' => $this->app])->loadAuth();

        if (file_exists(base_path('routes/web.php'))) {
            require base_path('routes/web.php');
        }
    }

    public function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new DisabledTestException());
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param string $uri
     *
     * @return $this
     */
    public function visit($uri)
    {
        if (is_callable('parent::visit')) {
            return parent::visit($uri);
        }

        return $this->get($uri);
    }

    /**
     * Assert that a given string is seen on the current HTML.
     *
     * @param string $text
     * @param bool   $negate
     *
     * @return $this
     */
    public function see($text, $negate = false)
    {
        if (is_callable('parent::see')) {
            return parent::see($text);
        }

        if ($negate) {
            return $this->assertDontSee($text);
        }

        return $this->assertSee($text);
    }
}
