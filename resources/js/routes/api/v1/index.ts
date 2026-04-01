import auth from './auth'
import organizations from './organizations'
import cloudProviders from './cloud-providers'
import infrastructures from './infrastructures'
import servers from './servers'

const v1 = {
    auth: Object.assign(auth, auth),
    organizations: Object.assign(organizations, organizations),
    cloudProviders: Object.assign(cloudProviders, cloudProviders),
    infrastructures: Object.assign(infrastructures, infrastructures),
    servers: Object.assign(servers, servers),
}

export default v1