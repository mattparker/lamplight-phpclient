<?php

namespace Lamplight\Record;

use PHPUnit\Framework\TestCase;

class WorkareaSummaryTest extends TestCase {


    public function test_get_record_values () {

        $sut = new WorkareaSummary([
            'id' => 43,
            'text' => 'Games',
            'children' => $child_items = [
                ['id' => 65, 'text' => 'tennis'],
                ['id' => 12, 'text' => 'netball']
            ]
        ]);


        $this->assertEquals(43, $sut->get('id'));
        $this->assertEquals('Games', $sut->get('text'));
        $this->assertEquals($child_items, $sut->get('children'));
        $this->assertEquals(3, $sut->count());
        $this->assertEquals('', $sut->get('not in data'));

        $i = 0;
        foreach ($sut as $key => $value) {

            switch ($i) {
                case 0:
                    $this->assertEquals('id', $key);
                    $this->assertEquals(43, $value);
                    break;
                case 1:
                    $this->assertEquals('text', $key);
                    $this->assertEquals('Games', $value);
                    break;
                case 2:
                    $this->assertEquals('children', $key);
                    $this->assertEquals($child_items, $value);
                    break;
            }
            $i++;
        }

    }

    public function test_render_record_no_template () {

        $sut = new WorkareaSummary([
            'id' => 43,
            'text' => '<b>Games<b/>',
            'children' => $child_items = [
                ['id' => 65, 'text' => 'tennis'],
                ['id' => 12, 'text' => 'netball']
            ]
        ]);

        $expected = '43, &lt;b&gt;Games&lt;b/&gt;, 65, tennis, 12, netball';
        $this->assertEquals($expected, $sut->render());

    }

    public function test_render_record_with_template () {

        $sut = new WorkareaSummary([
            'id' => 43,
            'text' => '<b>Games<b/>',
            'children' => $child_items = [
                ['id' => 65, 'text' => 'tennis'],
                ['id' => 12, 'text' => 'netball']
            ]
        ]);
        $template = 'Area {id}, text {text}, with sub categories {children}';

        $expected = 'Area 43, text &lt;b&gt;Games&lt;b/&gt;, with sub categories 65, tennis, 12, netball';
        $this->assertEquals($expected, $sut->render($template));

    }

}
