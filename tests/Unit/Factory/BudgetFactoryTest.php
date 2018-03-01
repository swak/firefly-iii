<?php
/**
 * BudgetFactoryTest.php
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

namespace Tests\Unit\Factory;


use FireflyIII\Factory\BudgetFactory;
use Tests\TestCase;

/**
 * Class BudgetFactoryTest
 */
class BudgetFactoryTest extends TestCase
{
    /**
     * Put in ID, return it.
     *
     * @covers \FireflyIII\Factory\BudgetFactory
     */
    public function testFindById()
    {
        $existing = $this->user()->budgets()->first();
        /** @var BudgetFactory $factory */
        $factory = app(BudgetFactory::class);
        $factory->setUser($this->user());

        $budget = $factory->find($existing->id, null);
        $this->assertEquals($existing->id, $budget->id);

    }

    /**
     * Put in name, return it.
     *
     * @covers \FireflyIII\Factory\BudgetFactory
     */
    public function testFindByName()
    {
        $existing = $this->user()->budgets()->first();
        /** @var BudgetFactory $factory */
        $factory = app(BudgetFactory::class);
        $factory->setUser($this->user());

        $budget = $factory->find(null, $existing->name);
        $this->assertEquals($existing->id, $budget->id);

    }

    /**
     * Put in unknown, get NULL
     *
     * @covers \FireflyIII\Factory\BudgetFactory
     */
    public function testFindUnknown()
    {
        /** @var BudgetFactory $factory */
        $factory = app(BudgetFactory::class);
        $factory->setUser($this->user());
        $this->assertNull($factory->find(null, 'I dont exist.'.rand(1,000)));
    }

    /**
     * Put in NULL, will find NULL.
     *
     * @covers \FireflyIII\Factory\BudgetFactory
     */
    public function testFindNull()
    {
        /** @var BudgetFactory $factory */
        $factory = app(BudgetFactory::class);
        $factory->setUser($this->user());

        $this->assertNull($factory->find(null, null));

    }

}