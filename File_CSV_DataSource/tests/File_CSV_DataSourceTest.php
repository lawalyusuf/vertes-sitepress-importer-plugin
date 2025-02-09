<?php
require_once 'PHPUnit/Framework.php';

require_once "File/CSV/DataSource.php";
require_once "File/CSV/tests/fixtures/csv.php";

/**
 * Test class for File_CSV_DataSource.
 */
class File_CSV_DataSourceTest extends PHPUnit_Framework_TestCase
{
    protected $csv;

    protected function setUp()
    {
        $this->csv = new File_CSV_DataSource;
    }

    protected function tearDown()
    {
        $this->csv = null;
    }

    public function test_uses_must_load_valid_files()
    {
        // must return true when a file is valid
        foreach (fix('valid_files') as $file => $msg) {
            $this->assertTrue($this->csv->load(path($file)), $msg);
        }
    }

    public function testSettings()
    {
        $new_delim = '>>>>';
        $this->csv->settings(array('delimiter' => $new_delim));

        $expected = array(
            'delimiter' => $new_delim,
            'eol' => ";",
            'length' => 999999,
            'escape' => '"'
        );

        $msg = 'settings where not parsed correctly!';
        $this->assertEquals($expected, $this->csv->settings, $msg);
    }

    public function testHeaders()
    {

        $this->csv->load(path('symmetric.csv'));
        $result = $this->csv->getHeaders();
        $this->assertEquals(fix('symmetric_headers'), $result);
    }

    public function testConnect()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(fix('symmetric_connection'), $this->csv->connect());
    }

    public function test_connect_must_return_emtpy_arr_when_not_aisSymmetric()
    {
        $this->assertTrue($this->csv->load(path('escape_ng.csv')));
        $this->assertEquals(array(), $this->csv->connect());
    }

    public function testSymmetric_OK()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->isSymmetric());
    }

    public function testSymmetric_NG()
    {
        $this->assertTrue($this->csv->load(path('asymmetric.csv')));
        $this->assertFalse($this->csv->isSymmetric());
    }

    public function testAsymmetry()
    {
        $this->assertTrue($this->csv->load(path('asymmetric.csv')));
        $result = $this->csv->getAsymmetricRows();
        $this->assertEquals(fix('asymmetric_rows'), $result);
    }

    public function testColumn()
    {
        $this->assertTrue($this->csv->load(path('asymmetric.csv')));
        $result = $this->csv->getColumn('header_c');

        $this->assertEquals(fix('expected_column'), $result);

    }

    public function testRaw_array()
    {
        $this->assertTrue($this->csv->load(path('raw.csv')));
        $this->assertEquals(fix('expected_raw'), $this->csv->getRawArray());
    }

    public function test_if_connect_ignores_valid_escaped_delims()
    {
        $this->assertTrue($this->csv->load(path('escape_ok.csv')));
        $this->assertEquals(fix('expected_escaped'), $this->csv->connect());
    }

    public function test_create_headers_must_generate_headers_for_symmetric_data()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->createHeaders('COL'));
        $this->assertEquals(fix('expected_headers'), $this->csv->getHeaders());
    }

    public function tets_create_headers_must_not_create_when_data_is_aisSymmetric()
    {
        $this->assertTrue($this->csv->load(path('asymmetric.csv')));
        $this->assertFalse($this->csv->createHeaders('COL'));
        $this->assertEquals(fix('original_headers'), $this->csv->getHeaders());
    }

    public function test_inject_headers_must_inject_headers_for_symmetric_data()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(fix('original_headers'), $this->csv->getHeaders());
        $this->assertTrue($this->csv->setHeaders(fix('expected_headers')));
        $this->assertEquals(fix('expected_headers'), $this->csv->getHeaders());
        $this->assertEquals(fix('symmetric_raw_data'), $this->csv->getRows());
    }

    public function test_inject_headers_must_not_inject_when_data_is_aisSymmetric()
    {
        $this->assertTrue($this->csv->load(path('asymmetric.csv')));
        $this->assertEquals(fix('original_headers'), $this->csv->getHeaders());
        $this->assertFalse($this->csv->setHeaders(fix('expected_headers')));
        $this->assertEquals(fix('original_headers'), $this->csv->getHeaders());
    }

    public function test_row_count_is_correct()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $expected_count = count(fix('symmetric_connection'));
        $this->assertEquals($expected_count, $this->csv->countRows());
    }

    public function test_row_fetching_returns_correct_result()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $expected = fix('eighth_row_from_symmetric');
        $this->assertEquals($expected, $this->csv->getRow(8));
    }

    public function test_row_must_be_empty_array_when_row_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(array(), $this->csv->getRow(-1));
        $this->assertEquals(array(), $this->csv->getRow(10));
    }

    public function test_connect_must_build_relationship_for_needed_headers_only()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $result = $this->csv->connect(array('header_a'));
        $this->assertEquals(fix('header_a_connection'), $result);
    }

    public function test_connect_must_return_empty_array_if_given_params_are_of_invalid_datatype()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(array(), $this->csv->connect('header_a'));
    }

    public function test_connect_should_ignore_non_existant_headers_AND_return_empty_array()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(array(), $this->csv->connect(array('non_existent_header')));
    }

    public function test_connect_should_ignore_non_existant_headers_BUT_get_existent_ones()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $result = $this->csv->connect(array('non_existent_header', 'header_a'));
        $this->assertEquals(fix('header_a_connection'), $result);
    }

    public function test_count_getHeaders()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(5, $this->csv->countHeaders());
    }

    public function test_raw_array_must_remove_empty_lines()
    {
        $this->assertTrue($this->csv->load(path('symmetric_with_empty_lines.csv')));
        $this->assertEquals(fix('symmetric_connection'), $this->csv->connect());
    }

    public function test_raw_array_must_remove_empty_records()
    {
        $this->assertTrue($this->csv->load(path('symmetric_with_empty_records.csv')));
        $this->assertEquals(fix('symmetric_connection'), $this->csv->connect());
    }

    public function test_range_of_rows_should_retrive_specific_rows_only()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $expected = fix('symmetric_range_of_rows');
        $this->assertEquals($expected, $this->csv->getRows(range(1, 2)));
    }

    public function test_non_existent_rows_in_range_should_be_ignored()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $expected = fix('symmetric_range_of_rows');
        $this->assertEquals($expected, $this->csv->getRows(array(22, 19, 1, 2)));
    }

    public function test_fist_row_must_be_zero()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(fix('first_row_from_symmetric'), $this->csv->getRow(0));
    }

    public function test_uses_must_flush_internal_data_when_new_file_is_given()
    {
        $this->assertTrue($this->csv->load(path('another_symmetric.csv')));
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(fix('symmetric_headers'), $this->csv->getHeaders());
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows());
        $this->assertEquals(fix('symmetric_raw_data'), $this->csv->getRawArray());
    }

    public function test_getCell()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(fix('first_symmetric_cell'), $this->csv->getCell(0, 0));
    }

    public function test_header_exists()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        foreach (fix('symmetric_headers') as $h) {
            $this->assertTrue($this->csv->hasColumn($h));
        }
    }

    public function test_header_exists_must_return_false_when_header_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertFalse($this->csv->hasColumn(md5('x')));
    }

    public function test_fill_getCell()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->fillCell(0, 0, 'hoge hoge'));
        $this->assertEquals('hoge hoge', $this->csv->getCell(0, 0));
    }

    public function test_coordinatable_must_return_true_when_coordinates_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->hasCell(0, 0));
        $this->assertFalse($this->csv->hasCell(-1, 0));
        $this->assertFalse($this->csv->hasCell(0, -1));
        $this->assertFalse($this->csv->hasCell(-1, -1));
        $this->assertFalse($this->csv->hasCell(1, 11));
    }

    public function test_fill_column_must_fill_all_values_of_a_getColumn()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $fh = fix('first_symmetric_header');
        $this->assertTrue($this->csv->fillColumn($fh, ''));
        $this->assertEquals(fix('empty_column'), $this->csv->getColumn($fh));
    }

    public function test_append_column_must_create_new_header_and_blank_values()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->appendColumn('extra'));
        $se = fix('symmetric_extra_header');
        $this->assertEquals($se, $this->csv->getHeaders());
        $this->assertEquals(fix('empty_column'), $this->csv->getColumn('extra'));
        $this->assertEquals(count($se), $this->csv->countHeaders());
    }

    public function test_symmetrize_must_convert_asymmetric_file()
    {
        $this->assertTrue($this->csv->load(path('asymmetric.csv')));
        $this->csv->symmetrize();
        $this->assertTrue($this->csv->isSymmetric());
    }

    public function test_symetrize_must_not_alter_symmetric_data()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->csv->symmetrize();
        $this->assertEquals(fix('symmetric_headers'), $this->csv->getHeaders());
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows());
        $this->assertEquals(fix('symmetric_raw_data'), $this->csv->getRawArray());
    }

    public function test_remove_column_must_remove_last_column_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->removeColumn('header_e'));
        $this->assertEquals(fix('symmetric_raw_data_with_last_colum_removed'), $this->csv->getRawArray());
    }

    public function test_remove_column_must_remove_second_column_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->removeColumn('header_b'));
        $this->assertEquals(fix('symmetric_raw_data_with_second_column_removed'), $this->csv->getRawArray());
    }

    public function test_remove_column_must_return_false_when_column_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertFalse($this->csv->removeColumn(md5('header_b')));
    }

    public function test_remove_row_must_remove_first_row_successfully_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->removeRow(0));
        $this->assertEquals(fix('symmetric_rows_without_first_row'), $this->csv->getRows());
    }

    public function test_remove_row_must_remove_only_third_row_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->removeRow(2));
        $this->assertEquals(fix('symmetric_rows_without_third_row'), $this->csv->getRows());
    }

    public function test_remove_row_must_return_false_and_leave_rows_intact_when_row_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertFalse($this->csv->removeRow(999999));
    }

    public function test_rows_must_return_all_rows_when_argument_is_not_an_array()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows('lasdjfklsajdf'));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows(true));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows(false));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows(1));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows(0));
    }

    public function test_has_row_must_return_true_when_a_row_exists()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->hasRow(1));
    }

    public function test_row_must_return_false_when_a_row_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertFalse($this->csv->hasRow(999999));
    }

    public function test_fill_row_must_fill_a_row_with_a_string_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('one_row_only.csv')));
        $this->assertTrue($this->csv->fillRow(0, 'hello'));
        $this->assertEquals(fix('rows_from_one_row_only_plus_one_filled_with_str_hello'), $this->csv->getRows());
    }

    public function test_fill_row_mus_fill_a_row_with_an_array_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('one_row_only.csv')));
        $this->assertTrue($this->csv->fillRow(0, $this->csv->getHeaders()));
        $this->assertEquals(fix('rows_from_one_row_only_plus_one_filled_with_arr_abc'), $this->csv->getRows());
    }

    public function test_fill_row_must_fill_a_row_with_a_number_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('one_row_only.csv')));
        $this->assertTrue($this->csv->fillRow(0, 1));
        $this->assertEquals(fix('rows_from_one_row_only_plus_one_filled_with_num_1'), $this->csv->getRows());
    }

    public function test_fill_row_must_not_change_anything_when_given_row_does_not_exist_and_return_false()
    {
        $this->assertTrue($this->csv->load(path('one_row_only.csv')));
        $this->assertFalse($this->csv->fillRow(999, 1));
        $this->assertEquals(fix('rows_from_one_row_only'), $this->csv->getRows());
    }

    public function test_fill_row_must_not_change_anything_when_given_value_is_other_than_string_int_or_array_and_return_false()
    {
        $this->assertTrue($this->csv->load(path('one_row_only.csv')));
        $this->assertFalse($this->csv->fillRow(0, new stdClass));
        $this->assertEquals(fix('rows_from_one_row_only'), $this->csv->getRows());
    }

    // be strict, no looking back from the end of array
    public function test_fill_row_must_return_false_when_negative_numbers_are_given()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertFalse($this->csv->fillRow(-1, 'xxx'));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows());
    }

    public function test_append_row_must_aggregate_a_row_fill_it_with_values_and_return_true()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->appendRow(fix('one_row_for_symmetric')));
        $this->assertEquals(fix('symmetric_rows_plus_one'), $this->csv->getRows());
    }

    public function test_walk_row_must_replace_values_in_a_row_by_using_a_callback_and_be_true()
    {
        $this->assertTrue($this->csv->load(path('one_row_only.csv')));
        $this->assertTrue($this->csv->walkRow(0, 'callback'));
        $this->assertEquals(fix('rows_from_one_row_only_plus_one_filled_with_num_1'), $this->csv->getRows());
    }

    public function test_walk_row_must_return_false_when_callback_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $non_existent_callback = md5('');
        $this->assertFalse($this->csv->walkRow(1, $non_existent_callback));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows());
        $this->assertEquals(fix('symmetric_headers'), $this->csv->getHeaders());
    }

    public function test_walk_column_must_replace_values_in_a_column_and_be_true()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $fh = fix('first_symmetric_header');
        $this->assertTrue($this->csv->walkColumn($fh, 'callback2'));
        $this->assertEquals(fix('empty_column'), $this->csv->getColumn($fh));
    }

    public function test_walk_column_must_return_false_when_callback_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $fh = fix('first_symmetric_header');
        $non_existent_callback = md5('');
        $this->assertFalse($this->csv->walkColumn($fh, $non_existent_callback));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows());
        $this->assertEquals(fix('symmetric_headers'), $this->csv->getHeaders());
    }

    public function test_walk_grid_must_replace_the_whole_data_set_and_be_true()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $this->assertTrue($this->csv->walkGrid('callback2'));
        $this->assertEquals(fix('symmetric_rows_empty'), $this->csv->getRows());
    }

    public function test_walk_grid_must_return_false_when_callback_does_not_exist()
    {
        $this->assertTrue($this->csv->load(path('symmetric.csv')));
        $non_existent_callback = md5('');
        $this->assertFalse($this->csv->walkGrid($non_existent_callback));
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows());
        $this->assertEquals(fix('symmetric_headers'), $this->csv->getHeaders());
    }

    public function test_constructor_must_be_equivalent_to_load()
    {
        $this->csv = new File_CSV_DataSource(path('symmetric.csv'));
        $result = $this->csv->getHeaders();
        $this->assertEquals(fix('symmetric_headers'), $result);
        $this->assertEquals(fix('symmetric_rows'), $this->csv->getRows());
    }

    public function test_must_append_row_when_csv_file_only_has_headers_and_array_is_passed_returning_true()
    {
        $this->csv = new File_CSV_DataSource(path('only_headers.csv'));
        $this->assertTrue($this->csv->appendRow(array(1, 2, 3)));
        $result = array(array('a' => 1, 'b' => 2, 'c' => 3));
        $this->assertEquals($result, $this->csv->connect());
    }

    public function test_must_append_row_when_csv_file_only_has_headers_and_string_is_passed_returning_true()
    {
        $this->csv = new File_CSV_DataSource(path('only_headers.csv'));
        $this->assertTrue($this->csv->appendRow('1'));
        $result = array(array('a' => '1', 'b' => '1', 'c' => '1'));
        $this->assertEquals($result, $this->csv->connect());
    }

    public function test_must_append_row_when_csv_file_only_has_headers_and_numeric_value_is_passed_returning_true()
    {
        $this->csv = new File_CSV_DataSource(path('only_headers.csv'));
        $this->assertTrue($this->csv->appendRow(1));
        $result = array(array('a' => 1, 'b' => 1, 'c' => 1));
        $this->assertEquals($result, $this->csv->connect());
    }

    public function test_must_use_headers_as_max_row_padding_when_headers_length_is_longer_than_all_rows_length()
    {
        $this->csv = new File_CSV_DataSource(path('longer_headers.csv'));
        $this->csv->symmetrize();
        $this->assertEquals(fix('longer_headers'), $this->csv->connect());
    }

}

?>
