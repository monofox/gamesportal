<?php
class Guestbook {

    /**
     * Get guestbook entries of specific page
     *
     * @param integer $page       The page you want to display
     * @param integer $numEntries Number of entries per page
     *
     * @return DbStatusHandler
     */
    public function showEntries($page = 0, $numEntries = 20) {
        $db = Database2::getInstance();

        $q = $db->q('SELECT * FROM %pguestbook LIMIT %i,20', $page * $numEntries);

        if ($q->hasData()) {
            foreach ($q->getData() as $v) {
                $dt = DateTime::createFromFormat('Y-m-d H:i:s', $v->timestamp);
                $v->timestamp = $dt->format('d.m.Y, H:i:s');
            }
            unset($v);
        }

        return $q;
    }

    /**
     * Get number of guestbook entries
     *
     * @return integer
     */
    public function getNumberOfEntries() {
        $db = Database2::getInstance();
        $number = 0;

        $q = $db->q('SELECT count(*) as num FROM %pguestbook');
        if ($q->hasData()) {
            $number = $q->getFirst()->num;
        }

        return $number;
    }

    public function create($name, $comment) {
        $sh = new StatusHandler();
        $name = trim($name);
        $comment = trim($comment);

        if (strlen($name) < 5) {
            $sh->addError('Name muss aus mindestens 5 Zeichen bestehen!');
        }

        if (strlen($comment) < 10) {
            $sh->addError('Bitte geben Sie einen gültigen Kommentar an.');
        }

        if (!$sh->issetErrorMsg()) {
            $db = Database2::getInstance();
            $q = $db->q(
                'INSERT INTO %pguestbook (name, comment, timestamp) VALUES (%s, %s, %s)',
                $name, $comment, date('Y-m-d H:i:s')
            );

            $sh->setStatus($q->getStatus());
            if ($sh->getStatus()) {
                $sh->addSuccess('Eintrag erfolgreich gespeichert.');
            } else {
                $sh->addError('Eintrag konnte nicht gespeichert werden.');
            }
        }

        return $sh;
    }
}
?>
