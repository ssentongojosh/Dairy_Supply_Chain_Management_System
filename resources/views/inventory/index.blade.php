@extends('layouts.contentNavbarLayout')
<!DOCTYPE html>
<html>
    <head>
    <title>Inventory List</title>
    <style>

        form.inline-form{
            display: none;
            border:1px solid #ccc;
            padding: 10px;
        }

        table{
            width : 80%;
            margin : 20px;
            border - collapse : collapse;
        }

        th, td {
            text-align:left;
            border:1px solid #999;
            padding:10px;
        }

        h1 {
            text-align:center;
        }
    </style>

    <script>
        function toggleForm(){
            const form = document.getElementById('createForm');
            form.style.display = (form.style.display === 'none') ? 'inline-block' : 'none';
        }
    </script>

    </head>
@section('content')
    <body>
        <h1>Products Inventory</h1>

        <form action="{{ route('inventory.search') }}" method="GET" style="margin-bottom:20px;">
            <input type="text" name="search" placeholder="Search for item.">
            <button type="submit">Search</button>
        </form>

        <div style="text-align:right;"><p><button type="button" onclick="toggleForm()">Add single Item</button>        |         
        <a href="{{ url('/inventory/create') }}">
        <button>➕ Add Many Items</button>
        </a></p></div>

        <form id ="createForm" action="{{ route ('inventory.store') }}" method="POST" class="inline-form">
            @csrf
            <label>Name: </label>
            <input type="text" name="name" required>

            <label>Quantity: </label>
            <input type="number" name="quantity" required>

            <label>Unit: </label>
            <input type="text" name="unit" required>
            
            <button type="submit">Add</button>
            <button type="button" onclick="toggleForm()">Close</button>
        </form>

        <table>
          <thead>
            <tr>
                <th>Name</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
          </thead>
            <tbody>
               
             @forelse ($inventory as $item)
                <tr>
                    <td>{{ $item ['name'] }}</td>
                    <td>{{ $item ['quantity'] }}</td>
                    <td>{{ $item ['unit'] }}</td>
                    <td>
                        @php
                          $status = $item->auto_status;
                          $color = match($status){
                            'available' => 'green',
                            'limited' => 'orange',
                            'out of stock' => 'red',
                          };
                        @endphp

                        <span style="color: {{$color}}; font-weight:bold;">
                            {{ ucfirst($status) }}
                        </span>

                        @if($item->is_reserved)
                            <span style="color:blue; font-weight:bold;">(R)</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('inventory.search', ['search' => $item->name]) }}"><button type="button">Edit</button></a> |
                        <form action="/inventory/{{ $item['id'] }}" method="POST" style="display:inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" onclick="return confirm('Are you sure!?')">Delete</button>
                        </form>
                    </td>
                </tr>

                @empty
                  <tr><td colspan="5" style="text-align:center;">No Items Found!</td></tr>
             @endforelse
            </tbody>
        </table>
    </body>
    @endsection
</html>