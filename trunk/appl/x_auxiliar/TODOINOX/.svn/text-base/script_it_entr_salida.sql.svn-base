select count(*) from item_entrada_bodega
where nom_producto = 'xxxx'
--982

update item_entrada_bodega
set nom_producto = isnull((select p.nom_producto from producto p where p.cod_producto = item_entrada_bodega.cod_producto), 'xxxx')
where nom_producto = 'xxxx'

select count(*) from item_entrada_bodega
where nom_producto = 'xxxx'
--0

select count(*) from item_salida_bodega
where nom_producto = 'xxxx'
--13907

update item_salida_bodega
set nom_producto = isnull((select p.nom_producto from producto p where p.cod_producto = item_salida_bodega.cod_producto), 'xxxx')
where nom_producto = 'xxxx'

select count(*) from item_salida_bodega
where nom_producto = 'xxxx'
--0