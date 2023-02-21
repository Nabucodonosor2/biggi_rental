ALTER FUNCTION [dbo].[f_origen_arriendo1](@ve_cod_doc numeric, @ve_tipo_doc varchar(20))
RETURNS varchar(max)
AS
BEGIN 
	declare 
	   @vl_origen  varchar(1),
       @vl_cod_item_doc  numeric,
       @vl_cod_doc  numeric,
       @lv_retorno varchar(100)
	
    IF (@ve_tipo_doc='ARRIENDO')
        BEGIN
            SELECT @vl_origen = ORIGEN_ARRIENDO FROM arriendo WHERE COD_ARRIENDO = @ve_cod_doc
	        IF(@vl_origen IS NULL)
            BEGIN
		        SET @lv_retorno = 'BIGGI'
            END    
	        ELSE IF(@vl_origen = 'C') 
		        SET @lv_retorno = 'CATERING'
        END
    ELSE IF (@ve_tipo_doc='FACTURA')
        BEGIN
            IF (@ve_cod_doc < 21954 )
                BEGIN
                    SET @lv_retorno = 'BIGGI'
                END
            ELSE
                BEGIN
                    SELECT TOP (1) @vl_cod_item_doc = COD_ITEM_DOC FROM item_factura WHERE COD_FACTURA = @ve_cod_doc
                    SELECT @vl_origen = ORIGEN_ARRIENDO FROM arriendo WHERE COD_ARRIENDO = @vl_cod_item_doc

	                IF(@vl_origen IS NULL)
                    BEGIN
		                SET @lv_retorno = 'BIGGI'
                    END    
	                ELSE IF(@vl_origen = 'C') 
		                SET @lv_retorno = 'CATERING'
                END
        END

    ELSE IF (@ve_tipo_doc='NOTA_CREDITO')
        BEGIN
            SELECT @vl_cod_doc = COD_DOC FROM NOTA_CREDITO WHERE COD_NOTA_CREDITO = @ve_cod_doc

            SELECT @lv_retorno = dbo.f_origen_arriendo1(@vl_cod_doc,'FACTURA')

        END

	ELSE IF (@ve_tipo_doc='ARRIENDOS_X_FACTURA')
        BEGIN
	        DECLARE @vl_cod_tipo_factura NUMERIC 
	        
	        select @vl_cod_tipo_factura = f2.COD_TIPO_FACTURA from FACTURA f2 where COD_FACTURA = @ve_cod_doc
	        if(@vl_cod_tipo_factura = 1)
	        	SET @lv_retorno = ''
	        ELSE BEGIN 
		        declare @vl_cant_arriendos NUMERIC 
		        
		        select @vl_cant_arriendos = count(DISTINCT i.COD_ITEM_DOC) from 
				FACTURA f, ITEM_FACTURA i
				where f.COD_FACTURA = @ve_cod_doc
				and i.COD_FACTURA = f.COD_FACTURA 

		        if(@vl_cant_arriendos > 1)BEGIN 
			        declare @vl_res				VARCHAR(max)
							,@vl_cod_item_doc_a	varchar(100)
				
					declare C_ITEM  cursor for 
					select CONVERT(varchar,COD_ITEM_DOC)
					from item_factura
					where COD_FACTURA = @ve_cod_doc
					group by COD_ITEM_DOC
				
					set @vl_res = ''
					open C_ITEM 
					fetch C_ITEM into @vl_cod_item_doc_a
					while @@fetch_status = 0 begin
						set @vl_res = @vl_res + '|' + @vl_cod_item_doc_a
						fetch C_ITEM into @vl_cod_item_doc_a
					end
					close C_ITEM
					deallocate C_ITEM
			        SET @lv_retorno = @vl_res 
		        END
				else if(@vl_cant_arriendos = 1)begin
					SELECT TOP (1) @lv_retorno = CONVERT(varchar,COD_ITEM_DOC) FROM item_factura WHERE COD_FACTURA = @ve_cod_doc
				end	        
	        END
        END
	ELSE IF(@ve_tipo_doc='ARRIENDOS_X_FACTURA2')BEGIN
		DECLARE @vl_count NUMERIC

		SELECT @vl_count = COUNT(*)
		FROM FACTURA_CONTRATO 
		WHERE COD_FACTURA = @ve_cod_doc

		if(@vl_count = 0)BEGIN
			set @lv_retorno = NULL
		END
		ELSE IF(@vl_count = 1)BEGIN
			SELECT @lv_retorno = COD_ARRIENDO
			FROM FACTURA_CONTRATO 
			WHERE COD_FACTURA = @ve_cod_doc
		END
		ELSE IF(@vl_count > 1)BEGIN
			set @lv_retorno = 'varios'
		END
	END
	return @lv_retorno     
END