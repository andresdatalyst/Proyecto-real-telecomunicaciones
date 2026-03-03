<h1>Prueba fusion Distribucion</h1>

<form method="POST" action="{{route('fusionDistribucion')}}">
@csrf
    <label for="id_cable_origen">id_cable_origen</label>
    <input type="number" name="id_cable_origen">
    
    <label for="city">ciudad</label>
    <input type="text" name="city">
    

    <button type="submit">hacer fusion</button>
</form>