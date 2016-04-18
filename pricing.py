import xmlrpclib
import pprint
api = xmlrpclib.ServerProxy('https://rpc.gandi.net/xmlrpc/')
apikey = 'YOURKEY'
r = api.catalog.list(apikey, {'product':{'type': 'domain', 'description': '.at'}})
pprint.pprint(r)
r = api.catalog.list(apikey, {'product':{'type': 'domain', 'description': '.asia'}},'TWD','C')
pprint.pprint(r)
r = api.catalog.list(apikey, {'product':{'type': 'domain', 'description': '.asia'},'action': {'name': 'create'}},'TWD','C')
pprint.pprint(r)
r = api.catalog.list(apikey, {'product':{'type': 'domain', 'description': '.asia'},'action': {'name': 'create','duration': 10}},'TWD','C')
pprint.pprint(r)
r = api.domain.price(apikey, ['gandi.sucks'])
pprint.pprint(r)
