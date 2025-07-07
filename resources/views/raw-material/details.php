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

<h2 style="text-align:center; color:blue;">Item Details</h2>
<div class="card" style="margin-left:200px;">
@if(session('message'))
     <div>{{ session('message') }}</div>
@endif 

<table>
<form id="editForm" action="{{ route('raw-inventory.update', $item->id ) }}" method="POST">
    @csrf
    @method('PUT')

    <tr>
        <td style="text-align:left;"><strong><label>Name:</label></strong></td>
        <td style="text-align:right;"><span id="viewName">{{ $item->name }}</span>
        <input type="text" name="name" id="inputName" value="{{ $item->name }}" style="display:none;"></td>
    </tr>

    <tr>
        <td style="text-align:left;"><strong><label>Quantity:</label></strong></td>
        <td style="text-align:right;"><span id="viewQuantity">{{ $item->quantity }}</span>
        <input type="number" name="quantity" id="inputQuantity" value="{{ $item->quantity }}" style="display:none;"></td>
        </tr>

    <tr>
        <td style="text-align:left;"><strong><label>Unit:</label></strong></td>
        <td style="text-align:right;"><span id="viewUnit">{{ $item->unit }}</span>
        <input type="text" name="unit" id="inputUnit" value="{{ $item->unit }}" style="display:none;"></td>
    </tr>

    <tr>
        <td style="text-align:left;"><strong><label>Status:</label></strong></td>
        @php
          $status = $item->auto_status;
          $color = match($status){
            'available' => 'green',
            'limited' => 'orange',
            'out of stock' => 'red',
          };
        @endphp

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
    <button type="button" id="updateButton" style="display:none;">Update</button>
    </div>

</form>
    
</div><br>

<button type="button" style="margin-left:200px;"><a href="{{ route('raw-inventory.index') }}">Back</a></button>

<script>
    document.getElementById('editButton').addEventListener('click', function(){
        //hide no edits
        document.getElementById('viewName').style.display = 'none';
        document.getElementById('viewQuantity').style.display = 'none';
        document.getElementById('viewUnit').style.display = 'none';

        //possible to edit
        document.getElementById('inputName').style.display = 'inline';
        document.getElementById('inputQuantity').style.display = 'inline';
        document.getElementById('inputUnit').style.display = 'inline';

        //buttons
        document.getElementById('editButton').style.display = 'none';
        document.getElementById('updateButton').style.display = 'inline';
    });
</script>

</body>

@endsection

</html>