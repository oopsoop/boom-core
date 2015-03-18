<?php

use Boom\Auth\PasswordGenerator\PasswordGenerator;
use Boom\Group;
use Boom\Person;

class Controller_Cms_Person_Save extends Controller_Cms_Person
{
    public function before()
    {
        parent::before();

        $this->_csrf_check();
    }

    public function action_add()
    {
        $password = PasswordGenerator::factory()->get_password();
        $encPassword = $this->auth->hash($password);

        $this->edit_person
            ->setName($this->request->post('name'))
            ->setEmail($this->request->post('email'))
            ->setEncryptedPassword($encPassword)
            ->save()
            ->addToGroup(Group\Factory::byId($this->request->post('group_id')));

        if (isset($password)) {
            $email = new Boom\Email\Newuser($this->edit_person, $password, $this->request);
            $email->send();
        }
    }

    public function action_add_group()
    {
        foreach ($this->request->post('groups') as $groupId) {
            $group = Group\Factory::byId($groupId);

            $this->log("Added person {$this->person->getEmail()} to group with ID {$group->getId()}");
            $this->edit_person->addToGroup($group);
        }
    }

    public function action_delete()
    {
        foreach ($this->request->post('people') as $personId) {
            $person = Person\Factory::byId($personId);

            $this->log("Deleted person with email address: " . $person->getEmail());
            $person->delete();
        }
    }

    public function action_remove_group()
    {
        $group = Group\Factory::byId($this->request->post('group_id'));

        $this->log("Edited the groups for person ".$this->edit_person->getEmail());
        $this->edit_person->removeFromGroup($group);
    }

    public function action_save()
    {
        $this->log("Edited user $this->edit_person->email (ID: $this->edit_person->id) to the CMS");

        $this->edit_person
            ->values($this->request->post(), ['name', 'enabled'])
            ->update();
    }
}