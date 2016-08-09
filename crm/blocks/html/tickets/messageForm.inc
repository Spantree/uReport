<?php
/**
 * @copyright 2011-2016 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 * @param Ticket $this->ticket
 */
use Application\Models\ContactMethodTable;
?>
<section class="messageForm">
    <header>
        <h1><?= $this->_('message_send'); ?></h1>
    </header>
	<form method="post" action="<?= BASE_URI; ?>/tickets/message">
		<fieldset>
			<input name="ticket_id" type="hidden" value="<?= $this->ticket->getId(); ?>" />
			<?php
                $helper = $this->template->getHelper('field');

                $options = [];
                $people = $this->ticket->getPeople(true);
                foreach ($people as $p) { $options[] = ['value'=>$p->getId(), 'label'=>self::escape($p->getFullname())]; }
                echo $helper->field([
                    'name'    => 'person_id',
                    'id'      => 'person_id',
                    'label'   => $this->_('person'),
                    'type'    => 'select',
                    'options' => $options
                ]);

                echo $helper->field([
                    'name'  => 'message',
                    'id'    => 'message',
                    'label' => $this->_('message'),
                    'type'  => 'textarea'
                ]);

				$helper = $this->template->getHelper('saveAndCancelButtons');
				echo $helper->saveAndCancelButtons($this->ticket->getURL());
			?>
		</fieldset>
	</form>
</section>