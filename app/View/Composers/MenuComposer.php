<?php
namespace App\View\Composers;

use App\Models\Menu;
use Illuminate\View\View;

class MenuComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $view->with([
            'mainMenu'          => Menu::getItems('main'),
            'footerDownloaders' => Menu::getItems('footer-downloaders'),
            'footerLegal'       => Menu::getItems('footer-legal'),
        ]);
    }
}
