CREATE FUNCTION f_get_month_text(@ve_valor	NUMERIC)
RETURNS VARCHAR(50)
AS
BEGIN
	DECLARE
		@ve_text	varchar(50)

	SELECT @ve_text = CASE @ve_valor
						WHEN 1 THEN 'ENERO'
						WHEN 2 THEN 'FEBRERO'
						WHEN 3 THEN 'MARZO'
						WHEN 4 THEN 'ABRIL'
						WHEN 5 THEN 'MAYO'
						WHEN 6 THEN 'JUNIO'
						WHEN 7 THEN 'JULIO'
						WHEN 8 THEN 'AGOSTO'
						WHEN 9 THEN 'SEPTIEMBRE'
						WHEN 10 THEN 'OCTUBRE'
						WHEN 11 THEN 'NOVIEMBRE'
						WHEN 12 THEN 'DICIEMBRE'
					  END	
	
	return @ve_text
END