# DataTables with Dynamic Filters

This project involves creating a data table using DataTables and Select2 libraries, with dynamic filtering options based on a CSV file's headers. The code dynamically determines the headers from the CSV file and allows the user to add multiple filters for the table.

## Features

- Displays data from a CSV file in a table.
- Dynamically determines and displays headers from the CSV file.
- Allows users to add multiple filters based on selected metrics.
- Filters support comparison operations (greater than, less than) and value ranges with different denominators (units, hundreds, thousands, millions, billions).
- Users can dynamically add or remove filter conditions.

## Libraries Used

- [jQuery](https://jquery.com/)
- [DataTables](https://datatables.net/)
- [Select2](https://select2.org/)

## How to Use

### Step 1: Prepare Your CSV File

Ensure you have a CSV file with appropriate headers. For example:

Company,Ticker,Sector,Industry,Revenue,GP,FCF,Capex
Company A,A,Tech,Software,1000000,500000,200000,100000
Company B,B,Finance,Banking,2000000,1000000,300000,150000

### Step 2: Upload the CSV File

Upload your CSV file to your WordPress site and set the file path using the `csv_filter_table_file` option in your WordPress settings.

### Step 3: Add the Shortcode to a Page or Post

Use the `[csv_filter_table]` shortcode in any post, page, or widget area where you want to display the table. For example:

```plaintext
[csv_filter_table]
