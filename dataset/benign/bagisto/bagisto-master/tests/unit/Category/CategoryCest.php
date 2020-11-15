<?php

namespace Tests\Unit\Category;

use UnitTester;
use Faker\Factory;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;
use Webkul\Core\Models\Locale;

class CategoryCest
{
    private $faker;

    /** @var Locale $localeEn */
    private $localeEn;

    /** @var Category $rootCategory */
    private $rootCategory;
    /** @var Category $category */
    private $category;
    /** @var Category $childCategory */
    private $childCategory;
    /** @var Category $grandChildCategory */
    private $grandChildCategory;

    private $rootCategoryAttributes;
    private $categoryAttributes;
    private $childCategoryAttributes;
    private $grandChildCategoryAttributes;

    public function _before(UnitTester $I)
    {
        $this->faker = Factory::create();

        $this->localeEn = $I->grabRecord(Locale::class, [
            'code' => 'en',
        ]);

        $rootCategoryTranslation = $I->grabRecord(CategoryTranslation::class, [
            'slug' => 'root',
            'locale' => 'en',
        ]);
        $this->rootCategory = $I->grabRecord(Category::class, [
            'id' => $rootCategoryTranslation->category_id,
        ]);

        $this->categoryAttributes = [
            'parent_id' => $this->rootCategory->id,
            'position' => 0,
            'status' => 1,
            $this->localeEn->code => [
                'name' => $this->faker->word,
                'slug' => $this->faker->slug,
                'description' => $this->faker->sentence(),
                'locale_id' => $this->localeEn->id,
            ],
        ];

        $this->category = $I->make(Category::class, $this->categoryAttributes)->first();
        $this->rootCategory->prependNode($this->category);
        $I->assertNotNull($this->category);

        $I->seeRecord(CategoryTranslation::class, [
           'category_id' => $this->category->id,
           'locale' => $this->localeEn->code,
           'url_path' => $this->category->slug,
        ]);

        $this->childCategoryAttributes = [
            'parent_id' => $this->category->id,
            'position' => 0,
            'status' => 1,
            $this->localeEn->code => [
                'name' => $this->faker->word,
                'slug' => $this->faker->slug,
                'description' => $this->faker->sentence(),
                'locale_id' => $this->localeEn->id,
            ],
        ];
        $this->childCategory = $I->make(Category::class, $this->childCategoryAttributes)->first();
        $this->category->prependNode($this->childCategory);
        $I->assertNotNull($this->childCategory);

        $expectedUrlPath = $this->category->slug . '/' . $this->childCategory->slug;
        $I->seeRecord(CategoryTranslation::class, [
            'category_id' => $this->childCategory->id,
            'locale' => $this->localeEn->code,
            'url_path' => $expectedUrlPath,
        ]);

        $this->grandChildCategoryAttributes = [
            'parent_id' => $this->childCategory->id,
            'position' => 0,
            'status' => 1,
            $this->localeEn->code => [
                'name' => $this->faker->word,
                'slug' => $this->faker->slug,
                'description' => $this->faker->sentence(),
                'locale_id' => $this->localeEn->id,
            ],
        ];
        $this->grandChildCategory = $I->make(Category::class, $this->grandChildCategoryAttributes)->first();
        $this->childCategory->prependNode($this->grandChildCategory);
        $I->assertNotNull($this->grandChildCategory);

        $expectedUrlPath .= '/' . $this->grandChildCategory->slug;
        $I->seeRecord(CategoryTranslation::class, [
            'category_id' => $this->grandChildCategory->id,
            'locale' => $this->localeEn->code,
            'url_path' => $expectedUrlPath,
        ]);

        $this->category->refresh();
        $this->childCategory->refresh();
    }

    public function testChildUrlPathIsUpdatedOnParentUpdate(UnitTester $I)
    {
        $newCategorySlug = $this->faker->slug;

        $this->categoryAttributes[$this->localeEn->code]['slug'] = $newCategorySlug;

        // Hacky trick to slow down the test because otherwise CategoryObserver method
        // won't work correctly (unit test is too fast)
        sleep(1);

        $I->assertTrue($this->category->update($this->categoryAttributes));

        $this->category->refresh();

        $I->seeRecord(CategoryTranslation::class, [
            'category_id' => $this->category->id,
            'locale' => $this->localeEn->code,
            'slug' => $newCategorySlug,
            'url_path' => $newCategorySlug,
        ]);

        $expectedUrlPath = $newCategorySlug . '/'
            . $this->childCategoryAttributes[$this->localeEn->code]['slug'];
        $I->seeRecord(CategoryTranslation::class, [
            'category_id' => $this->childCategory->id,
            'locale' => $this->localeEn->code,
            'url_path' => $expectedUrlPath,
        ]);

        $expectedUrlPath .= '/' . $this->grandChildCategoryAttributes[$this->localeEn->code]['slug'];
        $I->seeRecord(CategoryTranslation::class, [
            'category_id' => $this->grandChildCategory->id,
            'locale' => $this->localeEn->code,
            'url_path' => $expectedUrlPath,
        ]);

        $I->amGoingTo('test if the url_path attribute is available in the model');
        $this->grandChildCategory->refresh();
        $I->assertEquals($expectedUrlPath, $this->grandChildCategory->url_path);
    }

    public function testGetRootCategory(UnitTester $I)
    {
        $I->wantTo('test rootCategory attribute of a category');
        $rootCategory = $this->grandChildCategory->getRootCategory();

        $I->assertNotNull($rootCategory);
        $I->assertEquals($rootCategory->id, $this->rootCategory->id);
    }

    public function testGetPathCategories(UnitTester $I)
    {
        $I->wantTo('test getPathCategories is returning the correct categories in the correct order');
        $I->amGoingTo('get all categories wihin the path of the grand child category');
        $pathCategories = $this->grandChildCategory->getPathCategories();

        $I->assertCount(4, $pathCategories);
        $I->assertEquals($pathCategories[0]->id, $this->rootCategory->id);
        $I->assertEquals($pathCategories[1]->id, $this->category->id);
        $I->assertEquals($pathCategories[2]->id, $this->childCategory->id);
        $I->assertEquals($pathCategories[3]->id, $this->grandChildCategory->id);
    }
}
