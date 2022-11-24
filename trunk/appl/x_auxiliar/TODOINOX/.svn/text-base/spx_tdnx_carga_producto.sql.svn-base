alter PROCEDURE spx_tdnx_carga_producto
AS
BEGIN  
	insert into marca (nom_marca, orden)
	select distinct MARCA, 10000 from AUX_PRODUCTO_TODOINOX 
	where MARCA not in (select nom_marca from marca)
	
	update MARCA 
	set orden = cod_marca * 10
	where orden = 10000
	

	declare c_prod_4d cursor for 
	select   MODELO_EQUIPO
			,DESCRIPCION
			,ALTO               
			,ANCHO              
			,LARGO              
			,PESO             
			,LARGOE1                        
			,ANCHOE1                        
			,ALTOE1                         
			,PESOE1                         
			,PRECIO
			,FACTOR_VENTA                   
			,FACTORVTAINTER                 
			,PRECIOINTERNO                  
			,MANEJA_INVENTARIO   
		    ,COD_CLASIF_INV                 
			,MARCA         
			,DESCRIPTION                 
	from AUX_PRODUCTO_TODOINOX
--	where MODELO_EQUIPO = 'T-1/3'

	declare
		@vl_count				numeric
		,@vc_modelo_equipo		varchar(100)
		,@vc_DESCRIPCION		varchar(100)
		,@vc_ALTO               numeric(10,2)         
		,@vc_ANCHO              numeric(10,2)         
		,@vc_LARGO              numeric(10,2)         
		,@vc_PESO               numeric(10,2)         
		,@vc_LARGOE1            numeric(10,2)                     
		,@vc_ANCHOE1            numeric(10,2)         
		,@vc_ALTOE1             numeric(10,2)                     
		,@vc_PESOE1             numeric(10,2)                     
		,@vc_PRECIO				numeric
		,@vc_FACTOR_VENTA       numeric(10,2)                                 
		,@vc_FACTORVTAINTER     numeric(10,2)                                 
		,@vc_PRECIOINTERNO      numeric            
		,@vc_MANEJA_INVENTARIO	varchar(100)
		,@vc_COD_CLASIF_INV		varchar(100)
		,@vl_cod_clasif_inventario	numeric
		,@vc_MARCA              varchar(100)
		,@vl_cod_marca			numeric
		,@vl_es_compuesto		varchar(1)
		,@vc_DESCRIPTION		varchar(100)

	open c_prod_4d
	fetch c_prod_4d into @vc_modelo_equipo, @vc_DESCRIPCION, @vc_ALTO,@vc_ANCHO,@vc_LARGO,@vc_PESO,@vc_LARGOE1,@vc_ANCHOE1,@vc_ALTOE1,@vc_PESOE1, @vc_PRECIO
						,@vc_FACTOR_VENTA, @vc_FACTORVTAINTER,@vc_PRECIOINTERNO,@vc_MANEJA_INVENTARIO, @vc_COD_CLASIF_INV,@vc_MARCA,@vc_DESCRIPTION		

	while @@fetch_status = 0 begin
		select @vl_count = COUNT(*)
		from PRODUCTO
		where cod_producto = @vc_modelo_equipo
		
		if (@vl_count = 1) begin
			-- ya existe y solo lo marca como valido para todoinox
			update PRODUCTO
			set sistema_valido = SUBSTRING(sistema_valido, 1, 3) + 'S'
				,MANEJA_INVENTARIO = @vc_MANEJA_INVENTARIO
				,precio_venta_interno = @vc_PRECIOINTERNO
				,PRECIO_VENTA_PUBLICO = case PRECIO_VENTA_PUBLICO 
											when 0 then @vc_PRECIO
											else PRECIO_VENTA_PUBLICO
										end
			where cod_producto = @vc_modelo_equipo
		end
		else begin
			select @vl_cod_clasif_inventario = COD_CLASIF_INVENTARIO
			from CLASIF_INVENTARIO
			where NOM_CORTO_CLASIF_INVENTARIO = @vc_COD_CLASIF_INV
			if (@@rowcount=0)
				set @vl_cod_clasif_inventario = null
			
			select @vl_cod_marca = COD_MARCA
			from MARCA
			where NOM_MARCA = @vc_MARCA
			if (@@rowcount=0)
				set @vl_cod_marca = null
			
			IF (@vc_PRECIOINTERNO < 0)
				SET @vc_PRECIOINTERNO = 0
				
			-- lo debe crear
			insert into producto
				(COD_PRODUCTO               
				,NOM_PRODUCTO               
				,COD_TIPO_PRODUCTO          
				,COD_MARCA                  
				,NOM_PRODUCTO_INGLES        
				,LARGO                      
				,ANCHO                      
				,ALTO                       
				,PESO                       
				,LARGO_EMBALADO             
				,ANCHO_EMBALADO             
				,ALTO_EMBALADO              
				,PESO_EMBALADO              
				,FACTOR_VENTA_INTERNO       
				,PRECIO_VENTA_INTERNO       
				,FACTOR_VENTA_PUBLICO       
				,PRECIO_VENTA_PUBLICO       
				,USA_ELECTRICIDAD           
				,NRO_FASES                  
				,CONSUMO_ELECTRICIDAD       
				,RANGO_TEMPERATURA          
				,VOLTAJE                    
				,FRECUENCIA                 
				,NRO_CERTIFICADO_ELECTRICO  
				,USA_GAS                    
				,POTENCIA                   
				,POTENCIA_KW                
				,CONSUMO_GAS                
				,NRO_CERTIFICADO_GAS        
				,USA_VAPOR                  
				,CONSUMO_VAPOR              
				,PRESION_VAPOR              
				,USA_AGUA_FRIA              
				,USA_AGUA_CALIENTE          
				,CAUDAL                     
				,PRESION_AGUA               
				,DIAMETRO_CANERIA           
				,USA_VENTILACION            
				,VOLUMEN                    
				,CAIDA_PRESION              
				,DIAMETRO_DUCTO             
				,NRO_FILTROS                
				,USA_DESAGUE                
				,DIAMETRO_DESAGUE           
				,MANEJA_INVENTARIO          
				,STOCK_CRITICO              
				,TIEMPO_REPOSICION          
				,FOTO_GRANDE                
				,FOTO_CHICA                 
				,ES_DESPACHABLE             
				,PRECIO_LIBRE               
				,SISTEMA_VALIDO             
				,TIENE_CERTIFICADO_CESMEC   
				,OBTENER_DESDE4D            
				,ES_OFERTA                  
				,ES_RECICLADO               
				,PRECIO_OFERTA              
				,COD_CLASIF_INVENTARIO   
				,xCARGA_TODOINOX   
				)
			values
				(@vc_modelo_equipo			--COD_PRODUCTO               
				,@vc_DESCRIPCION			--NOM_PRODUCTO               
				,1							--COD_TIPO_PRODUCTO  = EQUIPO despues  se puede ir depurando
				,@vl_cod_marca				--COD_MARCA                  
				,@vc_DESCRIPTION			--NOM_PRODUCTO_INGLES        
				,isnull(@vc_LARGO,0)					--LARGO                      
				,isnull(@vc_ANCHO,0)					--ANCHO                      
				,isnull(@vc_ALTO,0)					--ALTO                       
				,isnull(@vc_PESO,0)					--PESO                       
				,isnull(@vc_LARGOE1,0)				--LARGO_EMBALADO             
				,isnull(@vc_ANCHOE1,0)				--ANCHO_EMBALADO             
				,isnull(@vc_ALTOE1,0)					--ALTO_EMBALADO              
				,isnull(@vc_PESOE1,0)					--PESO_EMBALADO              
				,@vc_FACTORVTAINTER			--FACTOR_VENTA_INTERNO       
				,@vc_PRECIOINTERNO			--PRECIO_VENTA_INTERNO       
				,@vc_FACTOR_VENTA			--FACTOR_VENTA_PUBLICO       
				,@vc_PRECIO					--PRECIO_VENTA_PUBLICO       
				,'N'						--USA_ELECTRICIDAD           
				,null						--NRO_FASES                  
				,null						--CONSUMO_ELECTRICIDAD       
				,null						--RANGO_TEMPERATURA          
				,null						--VOLTAJE                    
				,null						--FRECUENCIA                 
				,null						--NRO_CERTIFICADO_ELECTRICO  
				,'N'						--USA_GAS                    
				,null						--POTENCIA                   
				,null						--POTENCIA_KW                
				,null						--CONSUMO_GAS                
				,null						--NRO_CERTIFICADO_GAS        
				,'N'						--USA_VAPOR                  
				,null						--CONSUMO_VAPOR              
				,null						--PRESION_VAPOR              
				,'N'						--USA_AGUA_FRIA              
				,'N'						--USA_AGUA_CALIENTE          
				,null						--CAUDAL                     
				,null						--PRESION_AGUA               
				,null						--DIAMETRO_CANERIA           
				,'N'						--USA_VENTILACION            
				,null						--VOLUMEN                    
				,null						--CAIDA_PRESION              
				,null						--DIAMETRO_DUCTO             
				,null						--NRO_FILTROS                
				,'N'						--USA_DESAGUE                
				,null						--DIAMETRO_DESAGUE      
				,@vc_MANEJA_INVENTARIO		--MANEJA_INVENTARIO          
				,0							--STOCK_CRITICO     = se marca con 0 para indicar que se traspaso desde 4D         
				,null						--TIEMPO_REPOSICION 
				,null						--FOTO_GRANDE                
				,null						--FOTO_CHICA                 
				,'S'						--ES_DESPACHABLE             
				,'N'						--PRECIO_LIBRE               
				,'NNNS'						--SISTEMA_VALIDO             
				,'N'						--TIENE_CERTIFICADO_CESMEC   
				,'N'						--OBTENER_DESDE4D            
			    ,'N'						--ES_OFERTA                  
				,'N'						--ES_RECICLADO               
				,null						--PRECIO_OFERTA              
			    ,@vl_cod_clasif_inventario	--COD_CLASIF_INVENTARIO     
			    ,'S' -- Marca auxiliar para indicar que el producto fue creado por esta rutira 
				)
		end 

		select @vl_count = COUNT(*)
		from producto_compuesto
		where COD_PRODUCTO = @vc_modelo_equipo
		
		if (@vl_count = 0)
			set @vl_es_compuesto = 'N'
		else
			set @vl_es_compuesto = 'S'
		
		insert into PRODUCTO_LOCAL
			(COD_PRODUCTO        
			,ES_COMPUESTO        
			)
		values 
			(@vc_modelo_equipo		
			,@vl_es_compuesto
			)
		
		fetch c_prod_4d into @vc_modelo_equipo, @vc_DESCRIPCION, @vc_ALTO,@vc_ANCHO,@vc_LARGO,@vc_PESO,@vc_LARGOE1,@vc_ANCHOE1,@vc_ALTOE1,@vc_PESOE1, @vc_PRECIO
						,@vc_FACTOR_VENTA, @vc_FACTORVTAINTER,@vc_PRECIOINTERNO,@vc_MANEJA_INVENTARIO, @vc_COD_CLASIF_INV,@vc_MARCA,@vc_DESCRIPTION	
	end
	close c_prod_4d
	deallocate c_prod_4d
END

