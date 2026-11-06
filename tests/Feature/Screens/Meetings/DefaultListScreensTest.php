<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\DefaultList;

class DefaultListScreensTest extends MeetingScreenTestCase
{
    public function test_the_list_links_each_list_to_its_edition_and_removal(): void
    {
        $list = DefaultList::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.defaultlist.list')));

        $this->assertStringContainsString('Equipo de montaje', $html);
        $this->assertStringContainsString(route('secretary.defaultlist.edit', ['id' => $list->id]), $html);
        $this->assertStringContainsString('action="'.route('secretary.defaultlist.remove').'"', $html);
    }

    public function test_the_form_keeps_the_name_and_the_people(): void
    {
        $list = DefaultList::sole();

        $create = $this->assertScreen($this->as('secretario')->get(route('secretary.defaultlist.create')));
        $this->assertStringContainsString('name="name"', $create);
        $this->assertStringContainsString('name="users[]"', $create);

        $edit = $this->assertScreen($this->get(route('secretary.defaultlist.edit', ['id' => $list->id])));
        $this->assertStringContainsString('name="_id" value="'.$list->id.'"', $edit);
        $this->assertStringContainsString('value="Equipo de montaje"', $edit);
        $this->assertSame(2, substr_count($edit, '" selected'));
    }
}
