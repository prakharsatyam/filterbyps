<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataTables with Multiple Filters</title>
   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .filter-group {
            margin-bottom: 10px;
        }
        .filter-group label {
            margin-right: 5px;
        }
        .filter-group .remove-filter {
            margin-left: 10px;
            color: red;
            cursor: pointer;
        }
        #addFilter {
            cursor: pointer;
            color: blue;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <h1>Data Table with Multiple Filters</h1>
    <div id="filters">
        <div class="filter-group">
            <label for="metricFilter0">Select Metric:</label>
            <select class="metricFilter" id="metricFilter0" style="width: 200px;">
                <option value="">Select Metric</option>
            </select>

            <label for="comparisonFilter0">Comparison:</label>
            <select class="comparisonFilter" id="comparisonFilter0" style="width: 100px;">
                <option value="greater">Greater than</option>
                <option value="less">Less than</option>
            </select>

            <label for="valueFilter0">Value:</label>
            <input type="number" class="valueFilter" id="valueFilter0" placeholder="Value" style="width: 100px;">

            <label for="denominatorFilter0">Denominator:</label>
            <select class="denominatorFilter" id="denominatorFilter0" style="width: 150px;">
                <option value="1">Units</option>
                <option value="100">Hundreds</option>
                <option value="1000">Thousands</option>
                <option value="1000000">Millions</option>
                <option value="1000000000">Billions</option>
            </select>

            <span class="remove-filter" data-id="0">✖</span>
        </div>
    </div>
    <button id="addFilter">+ Add Filter</button>
    <button id="applyFilter">Apply Filters</button>
    
    <table id="example" class="display">
        <thead>
            <tr id="table-header">
            </tr>
        </thead>
        <tbody>
            <?php
            $file = get_option('csv_filter_table_file');
            if (($handle = fopen($file, "r")) !== FALSE) {
                $header = fgetcsv($handle, 1000, ",");
                echo "<script>var tableHeaders = " . json_encode($header) . ";</script>";
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    echo "<tr>";
                    foreach ($data as $cell) {
                        echo "<td>" . htmlspecialchars($cell) . "</td>";
                    }
                    echo "</tr>";
                }
                fclose($handle);
            }
            ?>
        </tbody>
    </table>

    <script>
    $(document).ready(function() {
        // Populate table headers
        var headerHtml = '';
        tableHeaders.forEach(function(header) {
            headerHtml += '<th>' + header + '</th>';
        });
        $('#table-header').html(headerHtml);

        // Populate metric filter options
        var metricOptions = '';
        tableHeaders.forEach(function(header) {
            if (['Revenue', 'GP', 'FCF', 'Capex'].includes(header)) {
                metricOptions += '<option value="' + header.toLowerCase() + '">' + header + '</option>';
            }
        });
        $('.metricFilter').html('<option value="">Select Metric</option>' + metricOptions);

        var table = $('#example').DataTable({
            "pageLength": 10,
            "pagingType": "full_numbers",
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
        });

        $('.metricFilter, .comparisonFilter, .denominatorFilter').select2();

        var filterCount = 1;

        $('#addFilter').on('click', function() {
            var newFilterGroup = `
                <div class="filter-group" id="filterGroup${filterCount}">
                    <label for="metricFilter${filterCount}">Select Metric:</label>
                    <select class="metricFilter" id="metricFilter${filterCount}" style="width: 200px;">
                        <option value="">Select Metric</option>
                        ${metricOptions}
                    </select>

                    <label for="comparisonFilter${filterCount}">Comparison:</label>
                    <select class="comparisonFilter" id="comparisonFilter${filterCount}" style="width: 100px;">
                        <option value="greater">Greater than</option>
                        <option value="less">Less than</option>
                    </select>

                    <label for="valueFilter${filterCount}">Value:</label>
                    <input type="number" class="valueFilter" id="valueFilter${filterCount}" placeholder="Value" style="width: 100px;">

                    <label for="denominatorFilter${filterCount}">Denominator:</label>
                    <select class="denominatorFilter" id="denominatorFilter${filterCount}" style="width: 150px;">
                        <option value="1">Unit</option>
                        <option value="100">Hundred</option>
                        <option value="1000">Thousand</option>
                        <option value="1000000">Million</option>
                        <option value="1000000000">Billion</option>
                    </select>

                    <span class="remove-filter" data-id="${filterCount}">✖</span>
                </div>
            `;

            $('#filters').append(newFilterGroup);
            $(`#metricFilter${filterCount}, #comparisonFilter${filterCount}, #denominatorFilter${filterCount}`).select2();
            filterCount++;
        });

        $('#filters').on('click', '.remove-filter', function() {
            var id = $(this).data('id');
            $(`#filterGroup${id}`).remove();
        });

        $('#applyFilter').on('click', function() {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var valid = true;
                $('.filter-group').each(function() {
                    var metric = $(this).find('.metricFilter').val();
                    var comparison = $(this).find('.comparisonFilter').val();
                    var value = parseFloat($(this).find('.valueFilter').val());
                    var denominator = parseInt($(this).find('.denominatorFilter').val());

                    if (metric && comparison && !isNaN(value) && denominator) {
                        var cellValue = parseFloat(data[tableHeaders.indexOf(metric)].replace(/,/g, '')) / denominator;
                        if (comparison === 'greater') {
                            if (cellValue <= value) {
                                valid = false;
                                return false;
                            }
                        } else if (comparison === 'less') {
                            if (cellValue >= value) {
                                valid = false;
                                return false;
                            }
                        }
                    }
                });
                return valid;
            });
            table.draw();
            $.fn.dataTable.ext.search.pop();
        });
    });
    </script>
</body>
</html>
