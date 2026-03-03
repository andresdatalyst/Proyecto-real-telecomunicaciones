<h1>Prueba fusion</h1>

<form method="POST" action="{{route('fusion')}}">

    <label for="id_cable_origen">id_cable_origen</label>
    <input type="number" name="id_cable_origen">
    
    <label for="id_cable_destino">id_cable_destino</label>
    <input type="number" name="id_cable_destino">
    
    <label for="id_tp_origen">id_tp_origen</label>
    <input type="number" name="id_tp_origen">

    <label for="id_tp_destino">id_tp_destino</label>
    <input type="number" name="id_tp_destino">
    

    <button type="submit">hacer fusion</button>
</form>