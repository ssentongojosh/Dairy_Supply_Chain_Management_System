<!DOCTYPE html>
<html>
    <head>

        <title>Add Inventory Item</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            form {
                max-width: 400px;
                margin: auto;
            }
            label {
                display: block;
                margin-bottom: 8px;
            }
            input[type="text"], input[type="number"] {
                width: 100%;
                padding: 8px;
                margin-bottom: 10px;
            }
            button {
                padding: 10px 15px;
                background-color: #28a745;
                color: white;
                border: 1px solid black;
                cursor: pointer;
            }
            .item-group{
                padding:10px;
                margin-bottom: 20px;
                border: 1px solid #ccc;
            }
            .remove{
                background-color:crimson;
                border:1px solid black;
            }
            table{
                border-collapse:collapse;
                width: 100%;
                table-layout:fixed;
            }
            h, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            }
        </style>
    </head>
    <body>
        <h1>Add New Item Form</h1>
        <form action="{{ route('inventory.store') }}" method="POST" id="multiForm">
            @csrf
        <table>
            <thead><tr>
                <th style="width:100px;">Name:</th>
                <th style="width:100px;">Quantity:</th>
                <th style="width:100px;">Unit:</th>
                <th style="width:150px;">Status:</th>
                <th style="width:200px;">Action:</th>
            </tr></thead>
            <tbody id="rows">
                <tr>
                    <td><input type="text" name="products[0][name]" required></td>  
                    <td><input type="number" name="products[0][quantity]" required></td> 
                    <td><input type="text" name="products[0][unit]" required></td>
                    <td>
                        <select id="status" name="products[0][status]" required>
                            <option value ="available">Available</option>
                            <option value ="out of stock">Out of Stock</option>
                            <option value ="reserved">Reserved</option>    
                         </select>
                    </td>
                    <td>
                        <button type="button" onclick="addItem()">Add item</button>
                        <button type="button" class="remove" onclick="removeRow(this)">Remove</button>
                    </td>
                </tr>  
            </tbody>
        </table>  
            <div id="items">
                <div class="item-group">
            <label for="name">Name:</label>
            


            
            <button type="submit">Save All</button>

        </form>
        <div style="text-align:right;">
            <a href="{{ route('inventory.index') }}"><button style="background-color: grey;">Back to Inventory List</button></a>
        </div>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
         @if (session('success'))
         <script>
            Swal.fire({
                icon:'success',
                title:'Success',
                text:'{{ session('success') }}',
                showConfirmationButton: false,
                timer:2000
            });
         </script>
         @endif

            <!-- ❌ Show validation errors -->
             @if ($errors->any())
             <sccript>
                Swal.fire({
                    icon:'error',
                    title:'Validation Error',
                    html:'{!! implode('<br>', $errors->all()) !!}',
                    confirmationText: 'ok'
                });
             </script>
             @endif

        <script>
            let rowIndex=1;
            function addRow(){
                const container = document.getElementById('rows');
                const row = document.createElement('tr');

                row.innerHTML = `
                <td><input type="text" name="products[${rowIndex}][name]" required></td>
                <td><input type="number" name="products[${rowIndex}][quantity]" required></td>
                <td><input type="text" name="products[${rowIndex}][unit]" required></td>
                <td>
                    <select name="products[${rowIndex}][status]" required>
                        <option value="available">Available</option>
                        <option value="limited">Limited</option>
                        <option value="out of stock">Out of Stock</option>
                        <option value="reserved">Reserved</option>
                    </select>
                </td>
                <td><button type="button" class="remove" onclick="removeRow(this)">×</button></td>
            `;

            container.appendChild(row);
            rowIndex++;
            }

            function removeRow(button){
                button.closest('tr').remove();
            }
        </script>

    </body>
</html>