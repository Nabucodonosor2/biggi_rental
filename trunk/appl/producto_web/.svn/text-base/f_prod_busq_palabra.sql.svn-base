ALTER FUNCTION [dbo].[f_prod_busq_palabra](@ve_string varchar(MAX))
RETURNS @List TABLE (item VARCHAR(8000))
BEGIN
DECLARE @sItem VARCHAR(8000)

DECLARE C_STRING CURSOR FOR 
	SELECT UPPER(item)
	FROM dbo.f_split(@ve_string, ' ') WHERE LEN(item) > 2
	
		
	declare @vc_string varchar(MAX)
		
OPEN C_STRING
FETCH C_STRING INTO @vc_string
WHILE @@FETCH_STATUS = 0 BEGIN

	set @vc_string = REPLACE(@vc_string,'à','A')
	set @vc_string = REPLACE(@vc_string,'á','A')
	set @vc_string = REPLACE(@vc_string,'è','E')
	set @vc_string = REPLACE(@vc_string,'é','E')
	set @vc_string = REPLACE(@vc_string,'ì','I')
	set @vc_string = REPLACE(@vc_string,'í','I')
	set @vc_string = REPLACE(@vc_string,'ò','O')
	set @vc_string = REPLACE(@vc_string,'ó','O')
	set @vc_string = REPLACE(@vc_string,'ù','U')
	set @vc_string = REPLACE(@vc_string,'ú','U')
	set @vc_string = REPLACE(@vc_string,'ü','U')
	set @vc_string = REPLACE(@vc_string,':','')

	INSERT INTO @List SELECT @vc_string
	
FETCH C_STRING INTO @vc_string
END
CLOSE C_STRING
DEALLOCATE C_STRING

RETURN
END