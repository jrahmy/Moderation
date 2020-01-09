<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Moderation;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;

/**
 * Handles installation, upgrades, and uninstallation of the add-on.
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    /**
     * Alters schema of core tables.
     */
    public function installStep1()
    {
        $sm = $this->schemaManager();

        $sm->alterTable('xf_conversation_message', function (Alter $table) {
            $table->addColumn('j_warning_id', 'int')->setDefault(0);
            $table
                ->addColumn('j_warning_message', 'varchar', 255)
                ->setDefault('');
        });

        $sm->alterTable('xf_report_comment', function (Alter $table) {
            $table->addColumn('j_ip_id', 'int')->setDefault(0);
            $table
                ->addColumn('reaction_score', 'int')
                ->unsigned(false)
                ->setDefault(0);
            $table->addColumn('reactions', 'blob')->nullable();
            $table->addColumn('reaction_users', 'blob');
        });
    }

    /**
     * Applies default permissions for a fresh install.
     */
    public function installStep2()
    {
        $this->applyDefaultPermissions();
    }

    /**
     * Applies schema changes for conversation warnings.
     */
    public function upgrade1000511Step1()
    {
        $sm = $this->schemaManager();
        $sm->alterTable('xf_conversation_message', function (Alter $table) {
            $table->addColumn('j_warning_id', 'int')->setDefault(0);
            $table
                ->addColumn('j_warning_message', 'varchar', 255)
                ->setDefault('');
        });
    }

    /**
     * Applies schema changes for report comment features.
     */
    public function upgrade1000511Step2()
    {
        $sm = $this->schemaManager();
        $sm->alterTable('xf_report_comment', function (Alter $table) {
            $table->addColumn('j_ip_id', 'int')->setDefault(0);
            $table
                ->addColumn('reaction_score', 'int')
                ->unsigned(false)
                ->setDefault(0);
            $table->addColumn('reactions', 'blob')->nullable();
            $table->addColumn('reaction_users', 'blob');
        });
    }

    /**
     * @param int   $previousVersion
     * @param array $stateChanges
     */
    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        if ($this->applyDefaultPermissions($previousVersion)) {
            $this->app
                ->jobManager()
                ->enqueueUnique(
                    'permissionRebuild',
                    'XF:PermissionRebuild',
                    [],
                    false
                );
        }
    }

    /**
     * Reverts schema changes of core tables.
     */
    public function uninstallStep1()
    {
        $sm = $this->schemaManager();
        $sm->alterTable('xf_conversation_message', function (Alter $table) {
            $table->dropColumns(['j_warning_id', 'j_warning_message']);
        });
    }

    /**
     * @param int|null $previousVersion
     *
     * @return bool
     */
    protected function applyDefaultPermissions($previousVersion = null)
    {
        $applied = false;

        if (!$previousVersion || $previousVersion < 1000211) {
            $this->applyGlobalPermission(
                'conversation',
                'warn',
                'forum',
                'warn'
            );
            $applied = true;
        }

        return $applied;
    }
}
