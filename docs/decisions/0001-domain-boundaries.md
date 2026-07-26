# ADR 0001: Modüler monolith

Laravel uygulaması başlangıçta modüler monolith kalacaktır. Tarif, içerik kökeni, medya, arama, AI ve topluluk servis sınırları interface/event ile ayrılır; dağıtık servisler ölçülmüş trafik ihtiyacı oluşmadan kurulmaz.
