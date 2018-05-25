<?php
/**
 * ChooseAccountsHandlerTest.php
 * Copyright (c) 2018 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tests\Unit\Support\Import\JobConfiguration\Bunq;


use Amount;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Models\Account;
use FireflyIII\Models\AccountType;
use FireflyIII\Models\ImportJob;
use FireflyIII\Models\TransactionCurrency;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Currency\CurrencyRepositoryInterface;
use FireflyIII\Repositories\ImportJob\ImportJobRepositoryInterface;
use FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

/**
 * Class ChooseAccountsHandlerTest
 */
class ChooseAccountsHandlerTest extends TestCase
{
    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testCCFalse(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'caha' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);
        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();

        $repository->shouldReceive('getConfiguration')->andReturn([])->once();

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        $this->assertFalse($handler->configurationComplete());
    }

    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testCCTrue(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'cahb' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);

        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();

        $repository->shouldReceive('getConfiguration')->andReturn(['mapping' => [0 => 1, 1 => 2]])->once();
        $repository->shouldReceive('setStage')->withArgs([Mockery::any(), 'go-for-import'])->once();

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        $this->assertTrue($handler->configurationComplete());
    }

    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testConfigureJob(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'cahc' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // data:
        $data = [
            'account_mapping' => [
                '1234' => '456',
            ],
        ];

        $config                    = [
            'accounts' => [
                0 => ['id' => 1234, 'name' => 'bunq'],
            ],
        ];
        $expected                  = $config;
        $expected['mapping'][1234] = 456;

        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);

        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();
        $repository->shouldReceive('getConfiguration')->andReturn($config)->times(2);
        $repository->shouldReceive('setConfiguration')->withArgs([Mockery::any(), $expected])->once();
        $accountRepos->shouldReceive('findNull')->withArgs([456])->andReturn(new Account)->once();

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        try {
            $this->assertCount(0, $handler->configureJob($data));
        } catch (FireflyException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testConfigureJobInvalidBunq(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'cahd' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // data:
        $data = [
            'account_mapping' => [
                '1234' => '456',
            ],
        ];

        $config                 = [
            'accounts' => [
                0 => ['id' => 1235, 'name' => 'bunq'],
            ],
        ];
        $expected               = $config;
        $expected['mapping'][0] = 456;

        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);

        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();
        $repository->shouldReceive('getConfiguration')->andReturn($config)->times(2);
        $repository->shouldReceive('setConfiguration')->withArgs([Mockery::any(), $expected])->once();
        $accountRepos->shouldReceive('findNull')->withArgs([456])->andReturn(new Account)->once();

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        try {
            $this->assertCount(0, $handler->configureJob($data));
        } catch (FireflyException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testConfigureJobInvalidLocal(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'cahe' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // data:
        $data = [
            'account_mapping' => [
                '1234' => '456',
            ],
        ];

        $config                    = [
            'accounts' => [
                0 => ['id' => 1234, 'name' => 'bunq'],
            ],
        ];
        $expected                  = $config;
        $expected['mapping'][1234] = 0;

        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);

        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();
        $repository->shouldReceive('getConfiguration')->andReturn($config)->times(2);
        $repository->shouldReceive('setConfiguration')->withArgs([Mockery::any(), $expected])->once();
        $accountRepos->shouldReceive('findNull')->withArgs([456])->andReturnNull()->once();

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        try {
            $this->assertCount(0, $handler->configureJob($data));
        } catch (FireflyException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testConfigureJobNoMapping(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'cahf' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // data:
        $data   = ['account_mapping' => []];
        $config = [
            'accounts' => [
                0 => ['id' => 1234, 'name' => 'bunq'],
            ],
        ];

        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);

        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();
        $repository->shouldReceive('getConfiguration')->andReturn($config)->times(1);

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        try {
            $messages = $handler->configureJob($data);
            $this->assertCount(1, $messages);
            $this->assertEquals('It seems you have not selected any accounts.', $messages->first());
        } catch (FireflyException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testGetNextData(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'cahg' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // data:
        $config = [
            'accounts' => [
                0 => ['id' => 1234, 'name' => 'bunq'],
            ],
        ];

        $collection = new Collection;
        $account    = $this->user()->accounts()->first();
        $euro       = TransactionCurrency::first();
        $collection->push($account);


        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);

        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();
        $repository->shouldReceive('getConfiguration')->andReturn($config)->times(1);
        $accountRepos->shouldReceive('getAccountsByType')->withArgs([[AccountType::ASSET]])->andReturn($collection)->once();
        $accountRepos->shouldReceive('getMetaValue')->withArgs([Mockery::any(), 'currency_id'])->andReturn('1');
        $currencyRepos->shouldReceive('findNull')->withArgs([1])->andReturn($euro)->once();

        $expected = [
            'accounts'       => $config['accounts'],
            'local_accounts' => [
                $account->id => [
                    'name' => $account->name,
                    'iban' => $account->iban,
                    'code' => $euro->code,
                ],
            ],
        ];

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        try {
            $data = $handler->getNextData();
            $this->assertEquals($expected, $data);
        } catch (FireflyException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    /**
     * @covers \FireflyIII\Support\Import\JobConfiguration\Bunq\ChooseAccountsHandler
     */
    public function testGetNextDataNull(): void
    {
        $job                = new ImportJob;
        $job->user_id       = $this->user()->id;
        $job->key           = 'cahg' . random_int(1, 1000);
        $job->status        = 'new';
        $job->stage         = 'new';
        $job->provider      = 'bunq';
        $job->file_type     = '';
        $job->configuration = [];
        $job->save();

        // data:
        $config = [
            'accounts' => [
                0 => ['id' => 1234, 'name' => 'bunq'],
            ],
        ];

        $collection = new Collection;
        $account    = $this->user()->accounts()->first();
        $euro       = TransactionCurrency::first();
        $collection->push($account);


        // mock stuff
        $repository    = $this->mock(ImportJobRepositoryInterface::class);
        $accountRepos  = $this->mock(AccountRepositoryInterface::class);
        $currencyRepos = $this->mock(CurrencyRepositoryInterface::class);

        // mock calls
        $repository->shouldReceive('setUser')->once();
        $accountRepos->shouldReceive('setUser')->once();
        $currencyRepos->shouldReceive('setUser')->once();
        $repository->shouldReceive('getConfiguration')->andReturn($config)->times(1);
        $accountRepos->shouldReceive('getAccountsByType')->withArgs([[AccountType::ASSET]])->andReturn($collection)->once();
        $accountRepos->shouldReceive('getMetaValue')->withArgs([Mockery::any(), 'currency_id'])->andReturn('1');
        $currencyRepos->shouldReceive('findNull')->withArgs([1])->andReturn(null)->once();
        Amount::shouldReceive('getDefaultCurrencyByUser')->once()->andReturn($euro);

        $expected = [
            'accounts'       => $config['accounts'],
            'local_accounts' => [
                $account->id => [
                    'name' => $account->name,
                    'iban' => $account->iban,
                    'code' => $euro->code,
                ],
            ],
        ];

        $handler = new ChooseAccountsHandler;
        $handler->setImportJob($job);
        try {
            $data = $handler->getNextData();
            $this->assertEquals($expected, $data);
        } catch (FireflyException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }


}