@extends('master::layouts/admin')

@section('content')

  <h1>Reporte de Ventas</h1>

  <div class="row">
    <div class="col-sm-6">
      <p>Aquí van la o las tareas pendientes y asignadas</p>
    </div>
    <div class="col-sm-6">
      <p>Observaciones de Proyectos Activos</p>
    </div>
  </div>
  @foreach($graphs as $graph_name => $graph)
  	<div id="{{ $graph_name }}" style="height: 500px; width: 100%;"></div>
  @endforeach
@endsection

@section('script')
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="http://code.highcharts.com/modules/exporting.js"></script>
  @foreach($graphs as $graph_name => $graph)
    @include('master::scripts.graph-'.$graph["type"].'-js', ['graph_name'=>$graph_name, 'column'=>$graph["name"], 'label'=>$graph["label"], 'graph_items'=>$graph["items"], 'graph_subitems'=>$graph["subitems"], 'graph_field_names'=>$graph["field_names"]])
  @endforeach
@endsection