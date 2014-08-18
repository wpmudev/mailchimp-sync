<?php

class WPMUDEV_Mailchimp_Mobile_API {
    public function __construct(WPMUDEV_Mailchimp_Sync_API $master) {
        $this->master = $master;
    }

}


