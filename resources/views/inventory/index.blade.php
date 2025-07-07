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

 @if(session('newItem'))
    <style>

        .modal-overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999;
    }

    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 300px;
      text-align: left;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .modal-content h3 {
      margin-top: 0;
    }

    .close-btn {
      float: right;
      cursor: pointer;
      color: red;
    }

    <div class="modal-overlay" id="popupModal">
    <div class="modal-content">
      <span class="close-btn" onclick="document.getElementById('popupModal').style.display='none'">×</span>

      <h3>✅ Product Added!</h3>
      <p><strong>Name:</strong> {{ session('newItem')->name }}</p>
      <p><strong>Quantity:</strong> {{ session('newItem')->quantity }}</p>
      <p><strong>Unit:</strong> {{ session('newItem')->unit }}</p>
    </div>
  </div>

  <script>
    setTimeout(() => {
      document.getElementById('popupModal').style.display = 'none';
    }, 4000);

    </style>
    
@endif 
    <body>
        <h1>Products Inventory</h1>

        <form action="{{ route('inventory.search') }}" method="GET" style="margin-bottom:20px;">
            <input type="text" name="search" placeholder="Search for item.">
            <button type="submit">Search</button>
        </form>

        <div style="text-align:right;"><button type="button" onclick="toggleForm()">➕ Add Item</button>               

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