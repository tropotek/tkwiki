<?php
namespace App\Console;

use App\Db\Content;
use App\Db\Page;
use App\Db\Secret;
use App\Db\User;
use Bs\Auth;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bs\Console\Console;
use Tk\Config;

/**
 * Basic self check of page and secret user access
 *
 * @todo Automate this test and cleanup after execution
 */
class WikiTest extends Console
{

    protected function configure(): void
    {
        $this->setName('wikitest')
            ->setAliases(['wt'])
            ->setDescription('Test WIKI system');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Config::isDev()) {
            $this->writeError('Error: Only run this command in a dev environment.');
            return self::FAILURE;
        }


        // Private pages: staff authors can view/edit/delete, only author can view
        // Staff pages: all staff can view/edit/delete, only staff can view
        // Member pages: all staff can view/edit/delete, only staff/members can view
        // Public pages: all staff can view/edit/delete, all can view

        // members cannot create pages only view them
        $editor = User::findByUsername('editor');
        if (!$editor) {
            $this->setup();
        }
        $this->getOutput()->writeln("Pages:");

        $admin = User::findByUsername('wikiadmin');
        $this->showPages($admin);

        $editor = User::findByUsername('editor');
        $this->showPages($editor);

        $staff = User::findByUsername('staff');
        $this->showPages($staff);

        $member = User::findByUsername('member');
        $this->showPages($member);

        // public pages
        $this->getOutput()->writeln("USER: none - PUBLIC");
        $pages = Page::findViewable([
            'permission' => Page::PERM_PUBLIC,
        ]);
        foreach ($pages as $i => $page) {
            $author = User::find($page->userId)->givenName;
            $canView = $page->canView(null) ? 'Yes' : 'No';
            $canEdit = $page->canEdit(null) ? 'Yes' : 'No';
            $this->getOutput()->writeln("  {$i}. {$page->title} [{$author} - ".Page::PERM_LIST[$page->permission]."] [view: $canView] [edit: $canEdit]");
        }

        $this->getOutput()->writeln("");
        $this->getOutput()->writeln("Secrets:");

        $this->showSecrets($admin);
        $this->showSecrets($editor);
        $this->showSecrets($staff);
        $this->showSecrets($member);

        $this->getOutput()->writeln("");
        return self::SUCCESS;
    }


    private function showSecrets(?User $user):void
    {
        $list = array_filter(User::PERMISSION_LIST, function ($k) use ($user) {
            return $user->hasPermission($k);
        }, ARRAY_FILTER_USE_KEY);
        $perms = implode(', ', $list);
        $this->getOutput()->writeln("USER: {$user->username} - {$user->type} - {$perms}");
        $perms = match (true) {
            $user->isStaff() => Secret::STAFF_PERMS,
            default => Secret::PERM_MEMBER
        };
        $filter = [
            'userId' => $user->userId,
            'permission' => $perms,
        ];
        if ($user->isAdmin()) $filter = [];
        $secrets = Secret::findViewable($filter);
        foreach ($secrets as $i => $secret) {
            $author = User::find($secret->userId)->givenName;
            $canView = $secret->canView($user) ? 'Yes' : 'No';
            $canEdit = $secret->canEdit($user) ? 'Yes' : 'No';
            $this->getOutput()->writeln("  {$i}. {$secret->name} [{$author} - ".Page::PERM_LIST[$secret->permission]."] [view: $canView] [edit: $canEdit]");
        }

    }

    private function showPages(?User $user):void
    {
        $list = array_filter(User::PERMISSION_LIST, function ($k) use ($user) {
            return $user->hasPermission($k);
        }, ARRAY_FILTER_USE_KEY);
        $perms = implode(', ', $list);
        $this->getOutput()->writeln("USER: {$user->username} - {$user->type} - {$perms}");
        $perms = match (true) {
            $user->isStaff() => Page::STAFF_PERMS,
            default => Page::MEMBER_VIEW_PERMS,
        };
        $filter = [
            'userId' => $user->userId,
            'permission' => $perms,
        ];
        if ($user->isAdmin()) $filter = [];
        $pages = Page::findViewable($filter);
        foreach ($pages as $i => $page) {
            $author = User::find($page->userId)->givenName;
            $canView = $page->canView($user) ? 'Yes' : 'No';
            $canEdit = $page->canEdit($user) ? 'Yes' : 'No';
            $this->getOutput()->writeln("  {$i}. {$page->title} [{$author} - ".Page::PERM_LIST[$page->permission]."] [view: $canView] [edit: $canEdit]");
        }

    }

    private function setup(): void
    {
        // create users
        // - staff / editor
        // - staff
        // - member
        // - null (public)

        // create pages with all 4 permissions
        //  - PERM_PRIVATE = 9;
        //  - PERM_STAFF   = 2;
        //  - PERM_MEMBER  = 1;
        //  - PERM_PUBLIC  = 0;

        $editor = new User();
        $editor->type = User::TYPE_STAFF;
        $editor->permissions = User::PERM_SYSADMIN | User::PERM_MANAGE_STAFF | User::PERM_MANAGE_MEMBERS;
        $editor->username = 'editor';
        $editor->password = Auth::hashPassword('password');
        $editor->email = 'editor@dev.ttek.org';
        $editor->givenName = 'Editor';
        $editor->familyName = '...';
        $editor->timezone = 'Australia/Melbourne';
        $editor->save();

        $staff = new User();
        $staff->type = User::TYPE_STAFF;
        $staff->permissions = User::PERM_MANAGE_MEMBERS;
        $staff->username = 'staff';
        $staff->password = Auth::hashPassword('password');
        $staff->email = 'staff@dev.ttek.org';
        $staff->givenName = 'Staff';
        $staff->familyName = '...';
        $staff->timezone = 'Australia/Melbourne';
        $staff->save();

        $member = new User();
        $member->type = User::TYPE_MEMBER;
        $member->username = 'member';
        $member->password = Auth::hashPassword('password');
        $member->email = 'member@dev.ttek.org';
        $member->givenName = 'Member';
        $member->familyName = '...';
        $member->timezone = 'Australia/Melbourne';
        $member->save();


        // Editor pages
        $p = new Page();
        $p->userId = $editor->userId;
        $p->title = 'Editor Staff Page';
        $p->permission = Page::PERM_STAFF;
        $p->save();
        $cn = new Content();
        $cn->userId = $editor->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Editor Staff Page</p>');
        $cn->save();

        $p = new Page();
        $p->userId = $editor->userId;
        $p->title = 'Editor Member Page';
        $p->permission = Page::PERM_MEMBER;
        $p->save();
        $cn = new Content();
        $cn->userId = $editor->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Editor Member Page</p>');
        $cn->save();

        $p = new Page();
        $p->userId = $editor->userId;
        $p->title = 'Editor Public Page';
        $p->permission = Page::PERM_PUBLIC;
        $p->save();
        $cn = new Content();
        $cn->userId = $editor->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Editor Public Page</p>');
        $cn->save();

        $p = new Page();
        $p->userId = $editor->userId;
        $p->title = 'Editor Private Page';
        $p->permission = Page::PERM_PRIVATE;
        $p->save();
        $cn = new Content();
        $cn->userId = $editor->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Editor Private Page</p>');
        $cn->save();



        $p = new Page();
        $p->userId = $staff->userId;
        $p->title = 'Staff Staff Page';
        $p->permission = Page::PERM_STAFF;
        $p->save();
        $cn = new Content();
        $cn->userId = $staff->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Staff Staff Page</p>');
        $cn->save();

        $p = new Page();
        $p->userId = $staff->userId;
        $p->title = 'Staff Member Page';
        $p->permission = Page::PERM_MEMBER;
        $p->save();
        $cn = new Content();
        $cn->userId = $staff->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Staff Member Page</p>');
        $cn->save();

        $p = new Page();
        $p->userId = $staff->userId;
        $p->title = 'Staff Public Page';
        $p->permission = Page::PERM_PUBLIC;
        $p->save();
        $cn = new Content();
        $cn->userId = $staff->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Staff Public Page</p>');
        $cn->save();

        $p = new Page();
        $p->userId = $staff->userId;
        $p->title = 'Staff Private Page';
        $p->permission = Page::PERM_PRIVATE;
        $p->save();
        $cn = new Content();
        $cn->userId = $staff->userId;
        $cn->pageId = $p->pageId;
        $cn->html = sprintf('<p>Staff Private Page</p>');
        $cn->save();

        // create secrets to test
        // - PERM_PRIVATE  = 9;
        // - PERM_STAFF    = 2;
        // - PERM_USER     = 1;

        $secret = new Secret();
        $secret->userId = $editor->userId;
        $secret->permission = Secret::PERM_PRIVATE;
        $secret->name = "Editor Private Secret";
        $secret->save();

        $secret = new Secret();
        $secret->userId = $editor->userId;
        $secret->permission = Secret::PERM_STAFF;
        $secret->name = "Editor Staff Secret";
        $secret->save();

        $secret = new Secret();
        $secret->userId = $editor->userId;
        $secret->permission = Secret::PERM_MEMBER;
        $secret->name = "Editor Member Secret";
        $secret->save();


        $secret = new Secret();
        $secret->userId = $staff->userId;
        $secret->permission = Secret::PERM_PRIVATE;
        $secret->name = "Staff Private Secret";
        $secret->save();

        $secret = new Secret();
        $secret->userId = $staff->userId;
        $secret->permission = Secret::PERM_STAFF;
        $secret->name = "Staff Staff Secret";
        $secret->save();

        $secret = new Secret();
        $secret->userId = $staff->userId;
        $secret->permission = Secret::PERM_MEMBER;
        $secret->name = "Staff Member Secret";
        $secret->save();


    }


}
