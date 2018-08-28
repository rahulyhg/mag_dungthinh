<?php

namespace Botble\Blog\Http\Controllers;

use Botble\ACL\Repositories\Interfaces\UserInterface;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Botble\Page\Repositories\Interfaces\PageInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SeoHelper;
use Theme;

class PublicController extends Controller
{

    /**
     * @param Request $request
     * @param PostInterface $postRepository
     * @param PageInterface $pageRepository
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @author QuocDung Dang
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getApiSearch(
        Request $request,
        PostInterface $postRepository,
        PageInterface $pageRepository,
        BaseHttpResponse $response
    )
    {
        $query = $request->get('q');
        if (!empty($query)) {
            $posts = $postRepository->getSearch($query);
            $pages = $pageRepository->getSearch($query);

            $data = [
                'items' => [
                    'Posts' => Theme::partial('search.post', compact('posts')),
                    'Pages' => Theme::partial('search.page', compact('pages')),
                ],
                'query' => $query,
                'count' => $posts->count() + $pages->count(),
            ];

            if ($data['count'] > 0) {
                return $response->setData(apply_filters(BASE_FILTER_SET_DATA_SEARCH, $data, 10, 1));
            }
        }
        return $response->setError(true)->setMessage(trans('core.base::layouts.no_search_result'));
    }

    /**
     * @param Request $request
     * @param PostInterface $postRepository
     * @return \Response
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getSearch(Request $request, PostInterface $postRepository)
    {
        SeoHelper::setTitle(__('Search result for: ') . '"' . $request->get('q') . '"')->setDescription(__('Search result for: ') . '"' . $request->get('q') . '"');

        $posts = $postRepository->getSearch($request->get('q'), 0, 12);

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Search result for: ') . '"' . $request->get('q') . '"', route('public.search'));
        return Theme::scope('search', compact('posts'))->render();
    }

    /**
     * @param $slug
     * @param UserInterface $userRepository
     * @return \Response
     * @author QuocDung Dang
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getAuthor($slug, UserInterface $userRepository)
    {
        $author = $userRepository->getFirstBy(['username' => $slug]);
        if (!$author) {
            return abort(404);
        }

        admin_bar()->registerLink('Edit this user', route('user.profile.view', $author->id));

        SeoHelper::setTitle($author->getFullName())->setDescription($author->about);
        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add($author->getFullName(), route('public.author', $slug));

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, USER_MODULE_SCREEN_NAME, $author);
        return Theme::scope('author', compact('author'), 'plugins.blog::themes.author')->render();
    }
}
