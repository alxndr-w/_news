<?php

namespace FriendsOfRedaxo\Neues\Cronjob;

use FriendsOfRedaxo\Neues\Entry;
use rex_cronjob;
use rex_i18n;

use function count;
use function sprintf;

class Publish extends rex_cronjob
{
    /**
     * @return bool
     */
    public function execute()
    {
        /* Collection von Neues-Einträgen, die noch nicht veröffentlicht sind, aber es sein sollten. (geplant) */
        $neues_entry_to_publish = Entry::query()->where('status', Entry::PLANNED)->where('publishdate', date('Y-m-d H:i:s'), '<')->find();
        $neues_entry_to_publish->setValue('status', Entry::ONLINE);
        if (!$neues_entry_to_publish->save()) {
            $this->setMessage(sprintf(rex_i18n::msg('neues_entry_publish_error'), count($neues_entry_to_publish)));
            return false;
        }

        $this->setMessage(sprintf(rex_i18n::msg('neues_entry_publish_success'), count($neues_entry_to_publish)));
        return true;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('neues_entry_publish_cronjob');
    }

    public function getParamFields()
    {
        return [];
    }
}
