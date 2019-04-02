<?php
    namespace models\tool;
    class Html {
        public static function table($data, $columnConfigs) {
            $headerRow = array ('<div class="divRow">');
            $filterRow = array ('<div class="divRow">');
            foreach ($columnConfigs as $columnConfig) {
                $headerRow[] = '<div>' . $columnConfig['header'] . '</div>';
                $filterHtml  = isset($columnConfig['filter']) && isset($columnConfig['name']) && $columnConfig['filter'] && $columnConfig['name'] ? (is_array($columnConfig['filter']) ? self::selectList($columnConfig['name'], $columnConfig['filter'], $columnConfig['filterDefault']) : self::input($columnConfig['name'], $columnConfig['filterDefault'])) . "<input type='button' value='#' style='width:1em;' onclick=\"sort('" . $columnConfig['name'] . "')\">" : '#';
                $filterRow[] = "<div>$filterHtml</div>";
            }
            $headerRow[] = '</div>';
            $filterRow[] = '</div>';
            $table       = array (
                '<div class="divTable">',
                join('', $headerRow),
                join('', $filterRow),
            );
            foreach ($data as $lineNo => $array) {
                $row = array ('<div class="divRow">');
                foreach ($columnConfigs as $columnConfig) {
                    if (isset($columnConfig['fun'])) {
                        $row[] = '<div>' . call_user_func($columnConfig['fun'], $array) . '</div>';
                    } else {
                        if (isset($columnConfig['value'])) {
                            $row[] = '<div>' . $columnConfig['value'] . '</div>';
                        } else {
                            $row[] = '<div></div>';
                        }
                    }
                }
                $row[]   = '</div>';
                $table[] = join('', $row);
            }
            $table[] = '</div>';
            return join('', $table);
        }

        public static function gridTable($data, $columnConfigs, $pageInfo, $inputData) {
            $inputPageNo  = isset($inputData['pageNo']) ? intval($inputData['pageNo']) : 1;
            $inputSortKey = isset($inputData['sortKey']) ? $inputData['sortKey'] : 'order by id desc';
            $pageTotal    = $pageInfo['pageTotal'];
            $total        = $pageInfo['total'];
            $size         = $pageInfo['size'];
            $pageNo       = $pageInfo['pageNo'];
            $html         = "
            <div>
                <label>分页:
                    <input type=\"number\" id=\"pageNo\" value=\"$inputPageNo\" style=\"font-size: 18px;width: 60px\" onchange=\"page(this.value)\">
                    <input type=\"hidden\" id=\"sortKey\" name=\"sortKey\" value=\"$inputSortKey\">
                </label>
                <label>共$pageTotal 页/$total 条/每$size 页/当前$pageNo 页</label>
                <label>";
            $ceil10       = ceil($pageNo / 10) * 10;
            $start        = $ceil10 - 10;
            $start        = $start < 1 ? 1 : $start;
            $end          = $ceil10 > $pageTotal ? $pageTotal : $ceil10;
            $end          = $end == $pageNo && $pageTotal > $end ? $end + 1 : $end;
            for ($start; $start <= $end; $start ++) {
                $html .= "<a onclick=\"page($start)\">[$start]</a>--";
            }
            '    </label>
            </div>';
            $html .= self::table($data, $columnConfigs);
            return $html . '
            <script>function page(pageNo) {
        form = document.createElement(\'form\');
        form.method = \'post\';
        input = document.createElement(\'input\');
        input.type = \'text\';
        input.name = \'pageNo\';
        input.value = pageNo || document.getElementById(\'pageNo\').value;
        form.appendChild(input);
        form.appendChild(document.getElementsByClassName(\'divTable\')[0].firstElementChild.nextElementSibling);
        form.appendChild(document.getElementById(\'sortKey\'));
        form.submit();
    }
    document.addEventListener(\'keydown\', function () {
        if (event.keyCode == 13) {
            page();
        }
    });
    function sort(key) {
        var sortKey = document.getElementById(\'sortKey\').value;
        document.getElementById(\'sortKey\').value = sortKey.search(key) === -1 ? \'order by \' + key + \' desc\' : (sortKey.search(\'asc\') === -1 ? \'order by \' + key + \' asc\' : \'order by \' + key + \' desc\');
    }
    </script>';
        }

        public static function radioList($name, $data, $default = false, $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            $list     = array ();
            $i        = 0;
            foreach ($data as $value => $label) {
                $isChecked = $checked = ($default == $value) ? 'checked="checked"' : '';
                $list[]    = "<label $htmlOpts><input type='radio' name='$name' id='$name$i' value='$value' $isChecked><strong>$strong</strong></label>";
                $i ++;
            }
            return join('', $list);
        }

        public static function checkboxList($name, $data, $default = false, $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            $list     = array ();
            $i        = 0;
            $default  = is_array($default) ? $default : array ($default);
            foreach ($data as $value => $label) {
                $isChecked = in_array($value, $default) ? 'checked="checked"' : '';
                $id        = str_replace('[', '_', str_replace(']', '', $name)) . '_' . $value;
                $list[]    = "<label $htmlOpts><input type='checkbox' name='$name' id='$id' value='$value' $isChecked><strong>$label</strong></label>";
                $i ++;
            }
            return join('', $list);
        }

        public static function input($name, $data = '', $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            $id       = str_replace('[', '_', str_replace(']', '', $name));
            return "<input type='text' name='$name' id='$id' value='$data' $htmlOpts>";
        }

        public static function link($label, $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            return "<a $htmlOpts>$label</a>";
        }

        public static function button($label, $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            return "<input type='button'  value='$label' $htmlOpts>";
        }

        public static function hidden($name, $data = '', $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            $id       = str_replace('[', '_', str_replace(']', '', $name));
            return "<input type='hidden' name='$name' id='$id' value='$data' $htmlOpts>";
        }

        public static function date($name, $data = '', $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            $id       = str_replace('[', '_', str_replace(']', '', $name));
            return "<input type='date' name='$name' id='$id' value='$data' $htmlOpts>";
        }

        public static function number($name, $data = '', $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            $id       = str_replace('[', '_', str_replace(']', '', $name));
            return "<input type='number' name='$name' id='$id' value='$data' $htmlOpts>";
        }

        public static function textarea($name, $data = '', $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            return "<textarea name='$name' id='$name'  $htmlOpts>$data</textarea>";
        }

        public static function selectList($name, $data, $default = false, $htmlOpts = array ()) {
            $htmlOpts = self::htmlOpts($htmlOpts);
            $id       = str_replace('[', '_', str_replace(']', '', $name));
            $html     = "<select name='$name' id='$id' $htmlOpts>";
            $checked  = '';
            foreach ($data as $value => $text) {
                $checked = (!$checked && $default == $value) ? 'selected="selected"' : '';
                $html .= "<option value='$value' $checked>$text</option>";
            }
            $html .= "</select>";
            return $html;
        }

        public static function htmlOpts($styleData) {
            $html = array ();
            foreach ($styleData as $optName => $optText)
                $html[] = "$optName=\"$optText\"";
            return join(' ', $html);
        }

        public static function jpgFile($name, $value = '', $src = '', $htmlOpts = array ()) {
            $htmlOpts  = self::htmlOpts($htmlOpts);
            $id        = str_replace('[', '_', str_replace(']', '', $name));
            $inputId   = $id . '_input';
            $inputFile = $id . '_file';
            $inputImg  = $id . '_img';
            $html      = '';
            $html .= "<div dataName='jpgFile' $htmlOpts>";
            $html .= '<div dataName="jpgFileImg">';
            $html .= "<img id='$inputImg' src='$src'>";
            $html .= '</div>';
            $html .= "<div dataName='jpgFileInput'>";
            $html .= "<input type='file' id='$inputFile' accept='image/jpeg,image/jpg2' src='$src'>";
            $html .= "<input type='hidden' name='$name' id='$inputId' value='$value'>";
            $html .= '</div>';
            $html .= '</div>';
            $html .= "
            <script>
            document.getElementById('$inputFile').addEventListener('change',function(){
                var fr = new FileReader();
                fr.onload = function (evt) {
                    document.getElementById('$inputImg').src = evt.target.result;
                    document.getElementById('$inputId').value = evt.target.result;
                }
                fr.readAsDataURL(this.files[0]);
            });
            </script>";
            return $html;
        }
    }


    ?>