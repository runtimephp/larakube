import RegisterController from './RegisterController'
import AuthTokenController from './AuthTokenController'
import OrganizationController from './OrganizationController'
import CloudProviderController from './CloudProviderController'
import InfrastructureController from './InfrastructureController'
import ServerController from './ServerController'
import SyncServerController from './SyncServerController'

const V1 = {
    RegisterController: Object.assign(RegisterController, RegisterController),
    AuthTokenController: Object.assign(AuthTokenController, AuthTokenController),
    OrganizationController: Object.assign(OrganizationController, OrganizationController),
    CloudProviderController: Object.assign(CloudProviderController, CloudProviderController),
    InfrastructureController: Object.assign(InfrastructureController, InfrastructureController),
    ServerController: Object.assign(ServerController, ServerController),
    SyncServerController: Object.assign(SyncServerController, SyncServerController),
}

export default V1