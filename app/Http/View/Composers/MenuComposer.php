<?php
// filepath: c:\xampp\htdocs\DSCMS\app\Http\View\Composers\MenuComposer.php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Enums\Role;
use Illuminate\Support\Facades\Log;

class MenuComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        $menuData = $this->getFilteredMenuData();
        $view->with('menuData', [$menuData]);
    }

    /**
     * Get filtered menu data based on user role
     */
    private function getFilteredMenuData()
    {
        try {
            $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
            $verticalMenuData = json_decode($verticalMenuJson);

            if (!$verticalMenuData) {
                Log::error('Failed to decode verticalMenu.json');
                return (object)['menu' => []];
            }

            Log::info('Menu JSON loaded successfully', ['menu_items_count' => count($verticalMenuData->menu)]);

            // Filter menu based on user role
            $filteredMenuData = $this->filterMenuByUserRole($verticalMenuData);

            Log::info('Menu filtering completed', ['filtered_items_count' => count($filteredMenuData->menu)]);

            return $filteredMenuData;
        } catch (\Exception $e) {
            Log::error('Error loading menu data: ' . $e->getMessage());
            return (object)['menu' => []];
        }
    }

    /**
     * Filter menu items based on the authenticated user's role
     */
    private function filterMenuByUserRole($menuData)
    {
        // Check authentication status
        if (!Auth::check()) {
            Log::info('User not authenticated in MenuComposer');
            return $this->getGuestMenu();
        }

        $user = Auth::user();

        Log::info('User is authenticated in MenuComposer', [
            'user_id' => $user->id,
            'user_role' => $user->role
        ]);

        if (!$user->role) {
            Log::warning('User has no role assigned', ['user_id' => $user->id]);
            return $this->getDefaultMenu();
        }

        $userRole = $user->role instanceof Role ? $user->role->value : (string)$user->role;

        Log::info('Filtering menu for user role', [
            'user_role' => $userRole,
            'user_id' => $user->id,
            'original_menu_count' => count($menuData->menu)
        ]);

        // Filter the menu items
        $filteredMenu = $this->filterMenuItems($menuData->menu, $userRole);

        Log::info('Menu filtering result', [
            'filtered_menu_count' => count($filteredMenu),
            'user_role' => $userRole
        ]);

        return (object)['menu' => $filteredMenu];
    }

    /**
     * Recursively filter menu items based on user role
     */
    private function filterMenuItems($menuItems, $userRole)
    {
        $filtered = [];

        foreach ($menuItems as $item) {
            // If no roles specified, show to everyone (backward compatibility)
            if (!isset($item->roles)) {
                $filtered[] = $item;
                continue;
            }

            // Check if item has role restrictions
            if (!in_array($userRole, $item->roles)) {
                continue;
            }

            // Create a copy of the item to avoid modifying the original
            $filteredItem = clone $item;

            // Filter submenu items if they exist
            if (isset($item->submenu) && is_array($item->submenu)) {
                $filteredSubmenu = $this->filterMenuItems($item->submenu, $userRole);
                $filteredItem->submenu = $filteredSubmenu;
            }

            $filtered[] = $filteredItem;
        }

        return $filtered;
    }

    /**
     * Return minimal menu for guest users
     */
    private function getGuestMenu()
    {
        return (object)['menu' => [
            (object)[
                'url' => 'auth/login-basic',
                'name' => 'Login',
                'icon' => 'menu-icon tf-icons ri-login-box-line',
                'slug' => 'auth-login-basic'
            ]
        ]];
    }

    /**
     * Return default menu for users without assigned roles
     */
    private function getDefaultMenu()
    {
        return (object)['menu' => [
            (object)[
                'url' => 'pages/account-settings-account',
                'name' => 'Account Settings',
                'icon' => 'menu-icon tf-icons ri-settings-2-line',
                'slug' => 'pages-account-settings-account'
            ]
        ]];
    }
}
