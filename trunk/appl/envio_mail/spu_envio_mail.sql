ALTER PROCEDURE [dbo].[spu_envio_mail](@ve_operacion				varchar(20)
								,@ve_cod_envio_mail			numeric(10) = null
								,@ve_cod_estado_envio_mail	numeric(10) = null
								,@ve_fecha_envio			datetime	= null    
							    ,@ve_mail_from				text		= null
							    ,@ve_mail_from_name			text		= null
							    ,@ve_mail_cc				text		= null
							    ,@ve_mail_cc_name			text		= null
							    ,@ve_mail_bcc				text		= null
							    ,@ve_mail_bcc_name			text		= null
							    ,@ve_mail_to				text		= null
							    ,@ve_mail_to_name			text		= null
							    ,@ve_mail_subject			text		= null
							    ,@ve_mail_body				text		= null
								,@ve_mail_altbody			text		= null
								,@ve_tipo_doc				varchar(100)= null
								,@ve_cod_doc				numeric(10) = null
								,@ve_xml_dte				text		 = null
								,@ve_usuario_dte			varchar(100)= null)
AS
BEGIN
	if (@ve_operacion='INSERT')BEGIN
		INSERT INTO ENVIO_MAIL (FECHA_REGISTRO
							   ,COD_ESTADO_ENVIO_MAIL
							   ,FECHA_ENVIO
							   ,MAIL_FROM
							   ,MAIL_FROM_NAME
							   ,MAIL_CC
							   ,MAIL_CC_NAME
							   ,MAIL_BCC
							   ,MAIL_BCC_NAME
							   ,MAIL_TO
							   ,MAIL_TO_NAME
							   ,MAIL_SUBJECT
							   ,MAIL_BODY
							   ,MAIL_ALTBODY
							   ,TIPO_DOC
							   ,COD_DOC)
					VALUES	   (getdate()
							   ,isnull(@ve_cod_estado_envio_mail, 1)
							   ,@ve_fecha_envio
							   ,@ve_mail_from
							   ,@ve_mail_from_name
							   ,@ve_mail_cc
							   ,@ve_mail_cc_name
							   ,@ve_mail_bcc
							   ,@ve_mail_bcc_name
							   ,@ve_mail_to
							   ,@ve_mail_to_name
							   ,@ve_mail_subject
							   ,@ve_mail_body
							   ,@ve_mail_altbody
							   ,isnull(@ve_tipo_doc, 'LLAMADO')
							   ,@ve_cod_doc)
	END						   
	else if (@ve_operacion='ENVIANDOSE')BEGIN
		update ENVIO_MAIL
		set COD_ESTADO_ENVIO_MAIL = 2	--Enviandose
		where COD_ENVIO_MAIL = @ve_cod_envio_mail
	END			
	else if (@ve_operacion='ENVIANDO')BEGIN
		update ENVIO_MAIL
		set COD_ESTADO_ENVIO_MAIL = 3	--Enviado
		where COD_ENVIO_MAIL = @ve_cod_envio_mail
	END	
	else if (@ve_operacion='ACUSE_DTE')BEGIN
		INSERT INTO ENVIO_MAIL (FECHA_REGISTRO
							   ,COD_ESTADO_ENVIO_MAIL
							   ,FECHA_ENVIO
							   ,MAIL_FROM
							   ,MAIL_FROM_NAME
							   ,MAIL_CC
							   ,MAIL_CC_NAME
							   ,MAIL_BCC
							   ,MAIL_BCC_NAME
							   ,MAIL_TO
							   ,MAIL_TO_NAME
							   ,MAIL_SUBJECT
							   ,MAIL_BODY
							   ,MAIL_ALTBODY
							   ,TIPO_DOC
							   ,COD_DOC
							   ,XML_DTE
							   ,USUARIO_DTE)
					VALUES	   (getdate()
							   ,isnull(@ve_cod_estado_envio_mail, 1)
							   ,@ve_fecha_envio
							   ,@ve_mail_from
							   ,@ve_mail_from_name
							   ,@ve_mail_cc
							   ,@ve_mail_cc_name
							   ,@ve_mail_bcc
							   ,@ve_mail_bcc_name
							   ,@ve_mail_to
							   ,@ve_mail_to_name
							   ,@ve_mail_subject
							   ,@ve_mail_body
							   ,@ve_mail_altbody
							   ,isnull(@ve_tipo_doc, 'LLAMADO')
							   ,@ve_cod_doc
							   ,@ve_xml_dte
							   ,@ve_usuario_dte)
			
		
	END						   
END