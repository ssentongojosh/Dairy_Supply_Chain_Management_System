@extends('layouts.contentNavbarLayout')
<!DOCTYPE html>
<html>
    <head>
        <title>Item Details</title>
        <style>
            body{
                justify-content:center;
                align-items:center;
            }
            .card{
                background-color:#fffddo;
                width:600px;
                padding:30px;
                border-radius:10px;
                border: 2px solid black;
                justify-content:center;
                text-align:center;
            }

        </style>
    </head>
  
<body>

@section('content') 

<h2 style="text-align:center; color:blue;">Products Details</h2>
<div class="card" style="margin-left:200px;">
@if(session('message'))
     <div>{{ session('message') }}</div>
@endif 

<table>
<form id="editForm" action="{{ route('inventory.update', $item->id ) }}" method="POST">
    @csrf
    @method('PUT')

    <tr>
        <td style="text-align:left;"><strong><label>ID:</label></strong></td>
        <td style="text-align:right;"><span id="viewId">{{ $item->id }}</span>
        <input type="text" name="id" id="inputId" value="{{ $item->id }}" style="display:none;"></td>
    </tr>

    <tr>
        <td style="text-align:left;"><strong><label>Name:</label></strong></td>
        <td style="text-align:right;"><span id="viewName">{{ $item->name }}</span>
        <input type="text" name="name" id="inputName" value="{{ $item->name }}" style="display:none;"></td>
    </tr>

    <tr>
        <td style="text-align:left;"><strong><label>Stock:</label></strong></td>
        <td style="text-align:right;"><span id="viewQuantity">{{ $item->quantity }}</span>
        <input type="number" name="quantity" id="inputQuantity" value="{{ $item->quantity }}" style="display:none;"></td>
        </tr>

    <tr>
        <td style="text-align:left;"><strong><label>Price:</label></strong></td>
        <td style="text-align:right;"><span id="viewPrice">{{ $item->price }}</span>
        <input type="number" name="price" id="inputPrice" value="{{ $item->price }}" style="display:none;"></td>
    </tr>

     <tr>
        <td style="text-align:left;"><strong><label>Added on:</label></strong></td>
        <td style="text-align:right;"><span id="viewAdded">{{ $item->manufacture_date }}</span>
        <input type="date" name="added" id="inputAdded" value="{{ $item->added }}" style="display:none;"></td>
    </tr>

    <tr>
        <td style="text-align:left;"><strong><label>Status:</label></strong></td>
        
        <td style="text-align:right;"><span id="viewStatus" class="status" style="color: {{ $color }};">
            {{ ucfirst($status) }}
            @if ($item->is_reserved){
              <span style="color:blue">(R)</span>
             } 
            @endif 
        </span></td>
    </tr>
</table><br><br> 
    
    <div>
    <button type="button" id="editButton">Edit</button>
    <button type="submit" id="updateButton" style="display:none;">Update</button>
    </div>

</form>
    
</div><br>

<button type="button" style="margin-left:200px;"><a href="{{ route('plant_manager.inventory') }}">Back</a></button>

<script>
    document.getElementById('editButton').addEventListener('click', function(){
        //hide no edits
        document.getElementById('viewId').style.display = 'none';
        document.getElementById('viewName').style.display = 'none';
        document.getElementById('viewQuantity').style.display = 'none';
        document.getElementById('viewPrice').style.display = 'none';
        document.getElementById('viewAdded').style.display = 'none';

        //possible to edit
        document.getElementById('inputId').style.display = 'inline';
        document.getElementById('inputName').style.display = 'inline';
        document.getElementById('inputQuantity').style.display = 'inline';
        document.getElementById('inputPrice').style.display = 'inline';
        document.getElementById('inputAdded').style.display = 'inline';

        //buttons
        document.getElementById('editButton').style.display = 'none';
        document.getElementById('updateButton').style.display = 'inline';
    });
</script>

</body>

@endsection

</html>