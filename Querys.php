<?PHP
include_once('conexion.php');
header('Access-Control-Allow-Origin: *');

//CAPTURA PARAMETRO DE GET 
$case=$_GET["case"];
$json=array();
//INSTANCIACION DE MI CONEXION A BBDD
$objConectar = new Conectar();
$conDb = $objConectar->getConnection();

switch ($case) {
    	case "productos":	
			
			$sql="SELECT p.*,c.Nombre_Categoria FROM productos p
			JOIN categorias_productos c ON p.Id_Categoria = c.Id_Categoria";
			$result = $conDb->prepare($sql);
			$rpta = $result->execute();
			
	        	
				while($row = $result->fetch(PDO::FETCH_ASSOC)){
			
						$item=array(
						"id" => $row["Id_Producto"],
						"Nombre"=> $row["Nombre_Producto"],
						"Marca" => $row["Marca_Producto"],
						"Referencia" => $row["Ref_Producto"],
						"Descripcion" => $row["Descripcion_Producto"],
						"Precio" => $row["Precio_Producto"],
						"Existencias" => $row["Existencia_Producto"],
						"Imagen" => $row["Imagen_Producto"],
						"Garantia" => $row["Garantia_Producto"],
						"Categoria"=>$row["Nombre_Categoria"],
						);
						$json['productos'][]=$item;
				}

			

     		break;

		case "usuarios":
			$sql="SELECT u.*, d.Nombre_Departamento , l.Nombre_Municipio FROM usuarios u
			JOIN localidad l ON u.Id_localidad = l.Id_Municipio
			JOIN departamentos d ON l.Id_Departamento = d.Id_Departamento";
			$result = $conDb->prepare($sql);
			$rpta = $result->execute();

			while($row =$result ->fetch(PDO::FETCH_ASSOC)){
			
				$item =array(
					"id" => $row["Id_Usuario"],
					"Nombre"=> $row["Nombre_Usuario"],
					"Apellido" => $row["Apellidos_Usuario"],
					"Email" => $row["Email_Usuario"],
					"Telefono" => $row["Telefono_Usuario"],
					"Ciudad" =>$row["Nombre_Municipio"],
					"Departamento" =>$row["Nombre_Departamento"],
					"Direccion" => $row["Direccion_Usuario"],
					"Contrasena" => $row["Password_Usuario"]
					
				);
				//AGREGAMOS AL ARRAY LOS DATOS ITERADOS
				$json['usuarios'][]=$item;
				//IMPRIMIMOS OBJETO JSON
				
			}
			break;

		case "proveedores":

			//EJECUTA DOS CONSULTAS, UNA: PROVEEDORES, DOS: PRODUCTOS DE PROVEEDORES
			$sql1="SELECT u.Id_Usuario,u.Nombre_Usuario,u.Apellidos_Usuario,u.Email_Usuario,u.Telefono_Usuario,u.Direccion_Usuario, 
			l.Nombre_Municipio,d.Nombre_Departamento
			FROM productos_proveedores pr
			JOIN usuarios u ON u.Id_Usuario = pr.Id_Proveedor
			JOIN localidad l ON u.Id_Usuario = l.Id_Municipio
			JOIN departamentos d ON d.Id_Departamento = l.Id_Departamento 
			GROUP BY u.Nombre_Usuario
            ORDER BY u.Id_Usuario";

			$sql2 = "SELECT u.Id_Usuario,p.Nombre_Producto,p.Marca_Producto,p.Descripcion_Producto,
			p.Imagen_Producto,p.Garantia_Producto,p.Existencia_Producto,p.Id_Producto
						,p.Precio_Producto FROM productos_proveedores pr
						JOIN usuarios u ON u.Id_Usuario = pr.Id_Proveedor
						JOIN productos p ON p.Id_Producto = pr.Id_Producto
						JOIN localidad l ON u.Id_Usuario = l.Id_Municipio
						JOIN departamentos d ON d.Id_Departamento = l.Id_Departamento 
						ORDER BY u.Id_Usuario";

			$result = $conDb->prepare($sql1);
			$result2 = $conDb->prepare($sql2);
			$rpta = $result->execute();
			$rpta2 = $result2->execute();
			$countArray = array();
			//GUARDAMOS EN ARRAY SEGUNDA CONSULTA (CONSULTA PRODUCTOS)
			while($row = $result2 ->fetch(PDO::FETCH_ASSOC)){
				
				$item = array(
					"id_usuario_productos" => $row["Id_Usuario"],
					"id_producto"=> $row["Id_Producto"],
					"nombre_producto"=> $row["Nombre_Producto"],
					"marca_producto"=> $row["Marca_Producto"],
					"descripcion_producto"=> $row["Descripcion_Producto"],
					"imagen_producto"=> $row["Imagen_Producto"],
					"garantia_producto"=> $row["Garantia_Producto"],
					"existencia_producto"=> $row["Existencia_Producto"],
					

				);
				$countArray[] = $item;

			}
			
			//RECORREMOS PRIMER CONSULTA (DATOS PROVEEDORES)
			while($row = $result ->fetch(PDO::FETCH_ASSOC)){
				//GUARDAMOS EN ARRAY DATOS DE PRIMER PROVEEDOR
				$condition = $row["Id_Usuario"];
					$item =array(

						"id" => $row["Id_Usuario"],
						"Nombre"=> $row["Nombre_Usuario"] . " " . $row["Apellidos_Usuario"],
						"Email" => $row["Email_Usuario"],
						"Telefono" => $row["Telefono_Usuario"],
						"Direccion" => $row["Direccion_Usuario"],
						"Ciudad" =>$row["Nombre_Municipio"],
						"Departamento" =>$row["Nombre_Departamento"],
				
					);
					$count = 0;
					//ITERA ARRAY DE PRODUCTOS
					while($count < count($countArray)){
						//COMPRUEBA QUE PRODUCTO PERTENECEN A ESTE PROVEEDOR, Y LOS GUARDA COMO
						// UN ARRAY
						if($countArray[$count]["id_usuario_productos"] == $condition){
							$itemDetail =array(
								"id_producto" => $countArray[$count]["id_producto"],
								"nombre_producto" => $countArray[$count]["nombre_producto"],
								"marca_producto" => $countArray[$count]["marca_producto"],
								"descripcion_producto" => $countArray[$count]["descripcion_producto"],
								"precio_producto"=>$countArray[$count]["id_usuario_productos"],
								"garantia_producto"=>$countArray[$count]["garantia_producto"],
								"existencia_producto"=>$countArray[$count]["existencia_producto"],
								"imagen_producto"=>$countArray[$count]["imagen_producto"],
							);
							
							$item ["productos"][] = $itemDetail;
						}
						$count++;
					}
					//GUARDAMOS EN JSON LOS DATOS COMPLETOS DE PROVEEDOR CON SUS PRODUCTOS
					
					$json['proveedores'][] = $item;	
					
				}
	
				
			
			
			
		break;

		

		case "pqrs":
			$sql="SELECT p.Id_PQRS, u.Nombre_Usuario,u.Apellidos_Usuario,
			p.Detalles_PQRS,ep.Razon_Estado,ep.Tipo_Estado 
			FROM usuarios u 
			JOIN pqrs p ON p.Id_Usuario = u.Id_Usuario 
			JOIN estado_pqrs ep ON p.Id_PQRS = ep.Id_PQRS";

			$result = $conDb->prepare($sql);
			$rpta = $result->execute();

			while($row =$result ->fetch(PDO::FETCH_ASSOC)){
				$item =array(
					"id" => $row["Id_PQRS"],
					"usuario"=> $row["Nombre_Usuario"]. " ".$row["Apellidos_Usuario"],
					"detalle" => $row["Detalles_PQRS"],
					"razon" => $row["Razon_Estado"],
					"estado" =>$row["Tipo_Estado"]
					
				);
				//AGREGAMOS AL ARRAY LOS DATOS ITERADOS
				$json['pqrs'][]=$item;
				//IMPRIMIMOS OBJETO JSON
			}
			break;

			case "productosOfertas":
				$sql="SELECT po.Id_Produc_Ofert,po.Precio_Produc_Ofert,
				po.Porcen_Oferta,po.Cant_Product_Ofert,po.Garantia_Product_Ofert,
				p.Nombre_Producto,p.Marca_Producto,p.Ref_Producto,
				p.Descripcion_Producto,p.Precio_Producto,p.Imagen_Producto,
				o.Caracteristicas_oferta,o.Fecha_Inicio,o.Fecha_Fin,o.Tipo_de_Oferta
				FROM productos p
				JOIN productos_ofertas po ON p.Id_Producto = po.Id_Producto
				JOIN ofertas o ON o.Id_Oferta = po.Id_Oferta";
	
				$result = $conDb->prepare($sql);
				$rpta = $result->execute();
	
				while($row =$result ->fetch(PDO::FETCH_ASSOC)){
					$item =array(
						"id" => $row["Id_Produc_Ofert"],
						"porcentaje_oferta"=> $row["Porcen_Oferta"],
						"referencia"=> $row["Ref_Producto"],
						"marca"=> $row["Marca_Producto"],
						"garantia"=> $row["Garantia_Product_Ofert"],
						"precio_oferta"=> $row["Precio_Produc_Ofert"],
						"precio_original" => $row["Nombre_Producto"],
						"nombre" => $row["Precio_Producto"],
						"descripcion_producto" => $row["Descripcion_Producto"],
						"tipo" => $row["Tipo_de_Oferta"],
						"fecha_inicio" =>$row["Fecha_Inicio"],
						"fecha_fin" =>$row["Fecha_Fin"],
						"imagen" =>$row["Imagen_Producto"],
						"caracteristicas_oferta" =>$row["Caracteristicas_oferta"]
						
					);
					//AGREGAMOS AL ARRAY LOS DATOS ITERADOS
					$json['productosOfertas'][]=$item;
					//IMPRIMIMOS OBJETO JSON
				}
				break;
			case "categorias":
				$sql="SELECT * FROM categorias_productos";
	
				$result = $conDb->prepare($sql);
				$rpta = $result->execute();
	
				while($row =$result ->fetch(PDO::FETCH_ASSOC)){
				
						$item =array(
							"id" => $row["Id_Categoria"],
							"Nombre"=> $row["Nombre_Categoria"],
									   
						);
					//AGREGAMOS AL ARRAY LOS DATOS ITERADOS
					$json['productosOfertas'][]=$item;
					//IMPRIMIMOS OBJETO JSON
					}
				break;

			case "pedidos":
				$sql1="SELECT p.Id_Pedido,p.Estado_Pedido,p.Fecha_Pedido, 
				(SELECT sum(Cantidad_Producto) 
				FROM detalle_pedidos) as cantidad_productos ,p.Valor_Total,
				u.Nombre_Usuario,u.Apellidos_Usuario,u.Id_Usuario 
				FROM pedidos p 
				JOIN usuarios u ON u.Id_Usuario = p.Id_Usuario 
				JOIN detalle_pedidos dp ON dp.Id_Pedido = p.Id_Pedido 
				JOIN productos pd ON pd.Id_Producto = dp.Id_Producto 
				GROUP BY p.Id_Pedido";

				$sql2 ="SELECT p.Nombre_Producto,p.Id_Producto,dp.Cantidad_Producto
				,dp.Precio_Producto,pd.Id_Pedido
				FROM productos p
				JOIN detalle_pedidos dp ON p.Id_Producto = dp.Id_Producto
				JOIN pedidos pd ON pd.Id_Pedido = dp.Id_Pedido";
				$resultPedidos = $conDb->prepare($sql1);
				$rpta = $resultPedidos->execute();

				$resultDetalles = $conDb->prepare($sql2);
				$resultDetalles->execute();

				while($row = $resultDetalles ->fetch(PDO::FETCH_ASSOC)){
				
					$item = array(
							"id_pedido" => $row["Id_Pedido"],
							"id_producto" => $row["Id_Producto"],
							"nombre_producto" => $row["Nombre_Producto"],
							"cantidad_producto" => $row["Cantidad_Producto"],
							"precio_producto"=>$row["Precio_Producto"],
						
					);
					$countArray[] = $item;
	
				}
				
				while($row =$resultPedidos ->fetch(PDO::FETCH_ASSOC)){
						$condition = $row['Id_Pedido'];
						$item =array(
							"id" => $condition,
							"estado"=> $row["Estado_Pedido"],
							"usuario"=> $row["Nombre_Usuario"] . " " . $row["Apellidos_Usuario"],
							"id_usuario"=> $row["Id_Usuario"],
							"fecha_pedido"=> $row["Fecha_Pedido"],
							"cantidad_productos"=> $row["cantidad_productos"],
							"total_a_pagar"=> $row["Valor_Total"],
									   
						);
						$count = 0;
						//ITERA ARRAY DE PRODUCTOS
						while($count < count($countArray)){
							//COMPRUEBA QUE PRODUCTO PERTENECEN A ESTE PROVEEDOR, Y LOS GUARDA COMO
							// UN ARRAY
							if($countArray[$count]["id_pedido"] == $condition){
								$itemDetail =array(
									"id_producto" => $countArray[$count]["id_producto"],
									"nombre_producto" => $countArray[$count]["nombre_producto"],
									"cantidad_producto" => $countArray[$count]["cantidad_producto"],
									"precio_producto"=>$countArray[$count]["precio_producto"],
								);
								$item ["productos"][] = $itemDetail;

							}
							$count++;
						}
						
				

					//AGREGAMOS AL ARRAY LOS DATOS ITERADOS
					$json['pedidos'][]=$item;
					//IMPRIMIMOS OBJETO JSON
					}
				break;
			
			case "envios":
				$sql="SELECT e.Id_Envio,e.Cobertura,e.Fecha_Entrega
				,e.Id_Pedido,p.Tipo_Pago,pd.Valor_Total,u.Nombre_Usuario,u.Apellidos_Usuario
				FROM envios e 
				JOIN pedidos pd ON pd.Id_Pedido = e.Id_Pedido
				JOIN usuarios u ON u.Id_Usuario = pd.Id_Usuario
				JOIN pagos p ON e.Id_Pago = p.Id_Pago";

				$result = $conDb->prepare($sql);
				$rpta = $result->execute();
	
				while($row = $result ->fetch(PDO::FETCH_ASSOC)){
				
						$item =array(
							"id" => $row["Id_Envio"],
							"usuario_a_enviar"=> $row["Nombre_Usuario"] . ' ' .$row["Apellidos_Usuario"],
							"cobertura"=> $row["Cobertura"],
							"fecha_entrega"=> $row["Fecha_Entrega"],
							"id_pedido"=> $row["Id_Pedido"],
							"pago"=> $row["Tipo_Pago"],
							"total"=> $row["Valor_Total"]
							
									   
						);
					//AGREGAMOS AL ARRAY LOS DATOS ITERADOS
					$json['envios'][]=$item;
					//IMPRIMIMOS OBJETO JSON
					}
				break;
				


}

		if(!empty($json) && $rpta!==false){
			echo json_encode($json);
		}else{
			echo json_encode(array("Rtpa001"=>"Sin resultado"));
		}
		
?>