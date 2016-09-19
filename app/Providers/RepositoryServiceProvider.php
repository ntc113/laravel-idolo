<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Post;
use App\Models\Category;
// use App\Models\Page;
// use App\Models\Feature;
// use App\Models\Faq;
// use App\Models\News;
// use App\Models\PhotoGallery;
// use App\Models\Project;
// use App\Models\Tag;
// use App\Models\Video;
// use App\Models\Menu;
// use App\Models\Slider;
// use App\Models\Partner;
// use App\Models\Setting;
use App\Repositories\Post\PostRepository;
use App\Repositories\Post\CacheDecorator as PostCacheDecorator;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\CacheDecorator as CategoryCacheDecorator;
// use App\Repositories\Feature\FeatureRepository;
// use App\Repositories\Feature\CacheDecorator as FeatureCacheDecorator;
// use App\Repositories\Page\PageRepository;
// use App\Repositories\Page\CacheDecorator as PageCacheDecorator;
// use App\Repositories\Faq\FaqRepository;
// use App\Repositories\Faq\CacheDecorator as FaqCacheDecorator;
// use App\Repositories\News\NewsRepository;
// use App\Repositories\News\CacheDecorator as NewsCacheDecorator;
// use App\Repositories\PhotoGallery\PhotoGalleryRepository;
// use App\Repositories\PhotoGallery\CacheDecorator as PhotoGalleryCacheDecorator;
// use App\Repositories\Project\ProjectRepository;
// use App\Repositories\Project\CacheDecorator as ProjectCacheDecorator;
// use App\Repositories\Tag\TagRepository;
// use App\Repositories\Tag\CacheDecorator as TagCacheDecorator;
// use App\Repositories\Video\VideoRepository;
// use App\Repositories\Video\CacheDecorator as VideoCacheDecorator;
// use App\Repositories\Menu\MenuRepository;
// use App\Repositories\Menu\CacheDecorator as MenuCacheDecorator;
// use App\Repositories\Slider\SliderRepository;
// use App\Repositories\Slider\CacheDecorator as SliderCacheDecorator;
// use App\Repositories\Partner\PartnerRepository;
// use App\Repositories\Partner\CacheDecorator as PartnerCacheDecorator;
// use App\Repositories\Setting\SettingRepository;
// use App\Repositories\Setting\CacheDecorator as SettingCacheDecorator;
use App\Services\Cache\AppCache;

/**
 * Class RepositoryServiceProvider.
 *
 * @author Sefa Karagöz <karagozsefa@gmail.com>
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $app = $this->app;

        // post
        $app->bind('App\Repositories\Post\PostInterface', function ($app) {

            $post = new PostRepository(
                new Post()
            );

            return $post;
        });

        // category
        $app->bind('App\Repositories\Category\CategoryInterface', function ($app) {

            $category = new CategoryRepository(
                new Category()
            );

            return $category;
        });
    }
}
