import React, { useState } from "react";
import {
  Button,
  Collapse,
  Modal,
  Input,
  Space,
  Typography,
  Divider,
  Switch,
  Upload,
  message,
  Row,
  Col
} from "antd";
import { UploadOutlined } from '@ant-design/icons';

const EditClassModal = ({ open, handleCancel, handleOk, modalData }) => {
  const [className, setClassName] = useState(modalData?.name);
  const [enableRecording, setEnableRecording] = useState(
    modalData?.record === "1"
  );
  const [accessCode, setAccessCode] = useState(modalData?.access_code);
  const [muteUserOnJoin, setMuteUserOnJoin] = useState(
    modalData?.mute_user_on_join === "1"
  );
  const [requireModeratorApproval, setRequireModeratorApproval] = useState(
    modalData?.require_moderator_approval === "1"
  );
  const [allUsersJoinAsModerator, setAllUsersJoinAsModerator] = useState(
    modalData?.all_users_join_as_moderator === "1"
  );
  const [logoUrl, setLogoUrl] = useState(modalData?.logo_url);
  const [logoutUrl, setLogoutUrl] = useState(modalData?.logout_url);
  const [primaryColor, setPrimaryColor] = useState(modalData?.primary_color);
  const [welcomeMessage, setWelcomeMessage] = useState(
    modalData?.welcome_message
  );
  const [enableModeratorToUnmuteUsers, setEnableModeratorToUnmuteUsers] =
    useState(modalData?.enable_moderator_to_unmute_users === "1");
  const [skipCheckAudio, setSkipCheckAudio] = useState(
    modalData?.skip_check_audio === "1"
  );
  const [disableListenOnlyMode, setDisableListenOnlyMode] = useState(
    modalData?.disable_listen_only_mode === "1"
  );
  const [enableUserPrivateChats, setEnableUserPrivateChats] = useState(
    modalData?.enable_user_private_chats === "1"
  );
  const [classLayout, setClassLayout] = useState(modalData?.class_layout);
  const [additionalJoinParams, setAdditionalJoinParams] = useState(
    modalData?.additional_join_params
  );
  const [loading, setLoading] = useState(false);
  const [presentation, setPresentation] = useState(modalData?.presentation);
  const [error, setError] = useState("");
  const [uploadingLogo, setUpLoadingLogo] = useState(false)

  const handleEditClass = async () => {
    try {
      setLoading(true);
      const classData = {
        name: className,
        record: enableRecording,
        presentation,
        access_code: accessCode,
        mute_user_on_join: muteUserOnJoin,
        require_moderator_approval: requireModeratorApproval,
        all_users_join_as_moderator: allUsersJoinAsModerator,
        logo_url: logoUrl,
        logout_url: logoutUrl,
        primary_color: primaryColor,
        welcome_message: welcomeMessage,
        enable_moderator_to_unmute_users: enableModeratorToUnmuteUsers,
        skip_check_audio: skipCheckAudio,
        disable_listen_only_mode: disableListenOnlyMode,
        enable_user_private_chats: enableUserPrivateChats,
        class_layout: classLayout,
        additional_join_params: additionalJoinParams,
      };
      const baseUrl = document
        .getElementById("rest-api")
        .getAttribute("data-rest-endpoint");
      const delimiter = document
        .getElementById("rest-api")
        .getAttribute("data-delimiter");

      const response = await fetch(
        `${baseUrl}/edit-class${delimiter}id=${modalData.id}`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(classData),
        }
      );
      if (!response.ok) {
        setError("Something went wrong. Please try again later.");
        return;
      }
      setError("");
      setLoading(false);
      handleOk({
        ...modalData,
        name: className,
        record: enableRecording,
        presentation,
        access_code: accessCode,
        mute_user_on_join: muteUserOnJoin,
        require_moderator_approval: requireModeratorApproval,
        all_users_join_as_moderator: allUsersJoinAsModerator,
        logo_url: logoUrl,
        logout_url: logoutUrl,
        primary_color: primaryColor,
        welcome_message: welcomeMessage,
        enable_moderator_to_unmute_users: enableModeratorToUnmuteUsers,
        skip_check_audio: skipCheckAudio,
        disable_listen_only_mode: disableListenOnlyMode,
        enable_user_private_chats: enableUserPrivateChats,
        class_layout: classLayout,
        additional_join_params: additionalJoinParams,
      });
    } catch (error) {
      setError("Something went wrong. Please try again later.");
      setLoading(false);
    }
  };

  const handleLogoUpload = async (file) => {
    const formData = new FormData();
    formData.append('file', file);

    try {
      const baseUrl = document.getElementById("rest-api").getAttribute("data-rest-endpoint");
      const response = await fetch(`${baseUrl}/upload-logo`, {
        method: 'POST',
        body: formData,
      });
      if (!response.ok) {
        setUpLoadingLogo(false)
        throw new Error('Failed to upload logo');
      }
      const result = await response.json();
      setLogoUrl(result.url);
      message.success('Logo uploaded successfully!');
      setUpLoadingLogo(false)
    } catch (error) {
      message.error('Failed to upload logo');
      setUpLoadingLogo(false)
    }
  };

  return (
    <Modal
      title=""
      open={open}
      onOk={handleCancel}
      okButtonProps={{
        onClick: handleEditClass,
        disabled: !className || loading,
        loading,
      }}
      onCancel={handleCancel}
      cancelButtonProps={{
        disabled: loading,
      }}
      okText={"Save"}
      cancelText="Cancel"
    >
      <div style={{ paddingBottom: "2rem" }}>
        <section>
          <Typography.Title level={5}>Class Settings</Typography.Title>
          <Space direction="vertical" size="large" style={{ width: "100%" }}>
            <div>
              <label>Class Name</label>
              <Input
                placeholder="Enter Class Name"
                disabled={loading}
                value={className}
                onChange={(e) => {
                  setClassName(e.target.value);
                }}
              />
            </div>
            <div>
              <label>Access Code(Optional)</label>
              <Input
                placeholder="Enter Class Access Code"
                disabled={loading}
                value={accessCode}
                onChange={(e) => {
                  setAccessCode(e.target.value);
                }}
              />
            </div>
            <div>
              <label>Presentation URL</label>
              <Input
                placeholder="Enter Presentation URL"
                value={presentation}
                disabled={loading}
                onChange={(e) => {
                  setPresentation(e.target.value);
                }}
              />
              <Typography.Text type="secondary">
                Upload the presentations to Media Library and paste the URL
                here.
              </Typography.Text>
            </div>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <label>Enable Recording</label>
              <Switch
                disabled={loading}
                checked={enableRecording}
                onChange={(checked) => setEnableRecording(checked)}
              />
            </div>

            {/* mute_user_on_join */}
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <label>Mute User On Join</label>
              <Switch
                disabled={loading}
                checked={muteUserOnJoin}
                onChange={(checked) => setMuteUserOnJoin(checked)}
              />
            </div>
            {/* require_moderator_approval */}
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <label>Require Moderator Approval</label>
              <Switch
                disabled={loading}
                checked={requireModeratorApproval}
                onChange={(checked) => setRequireModeratorApproval(checked)}
              />
            </div>
            {/* all_users_join_as_moderator */}
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <label>All Users Join As Moderator</label>
              <Switch
                disabled={loading}
                checked={allUsersJoinAsModerator}
                onChange={(checked) => setAllUsersJoinAsModerator(checked)}
              />
            </div>

            {/* branding settings */}
            <div>
              <Divider />
              <Collapse
                bordered={false}
                items={[
                  {
                    key: "1",
                    label: <h4 style={{ margin: 0 }}>Branding Settings</h4>,
                    children: (
                      <Space direction="vertical" size="large" style={{ width: "100%" }}>
                        <div>
                          <label>Logo URL(Optional)</label>
                          <div>
                            <Row gutter={16}>
                              <Col span={14}>

                                <Input
                                  placeholder="Enter Logo URL"
                                  value={logoUrl}
                                  disabled={loading}
                                  onChange={(e) => setLogoUrl(e.target.value)}
                                />

                              </Col>
                              <Col>
                                <Upload
                                  beforeUpload={(file) => {
                                    handleLogoUpload(file);
                                    setUpLoadingLogo(true)
                                    return false; // Prevent default upload behavior
                                  }}
                                  showUploadList={false}
                                  accept="image/*"
                                >
                                  {
                                    uploadingLogo ? <Button loading iconPosition="end">
                                      Uploading
                                    </Button> : <Button icon={<UploadOutlined />}>
                                      Upload Logo
                                    </Button>

                                  }
                                </Upload>
                              </Col>
                            </Row>
                          </div>
                          <Typography.Text type="secondary">
                            Logo will be displayed in the top left corner of the
                            BigBlueButton client only if it is enabled in the
                            server.
                          </Typography.Text>

                        </div>

                        {/* logout_url */}
                        <div>
                          <label>Logout URL(Optional)</label>
                          <Input
                            placeholder="Enter Logout URL"
                            value={logoutUrl}
                            disabled={loading}
                            onChange={(e) => setLogoutUrl(e.target.value)}
                          />
                          <Typography.Text type="secondary">
                            The logout URL is the URL that the BigBlueButton
                            client will redirect to when the user clicks the
                            logout button.
                          </Typography.Text>
                        </div>
                        {/* primary_color */}
                        <div>
                          <label>Primary Color(Optional)</label>
                          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                            <Input
                              style={{ width: "90%" }}
                              placeholder="Enter Primary Color"
                              value={primaryColor}
                              disabled={loading}
                              onChange={(e) => setPrimaryColor(e.target.value)}
                            />
                            <div
                              style={{
                                width: "2rem",
                                height: "2rem",
                                borderRadius: "50%",
                                backgroundColor: primaryColor,
                                border: "1px solid #ccc",
                              }}
                            ></div>
                          </div>
                        </div>
                        {/* welcome_message */}
                        <div>
                          <label>Welcome Message(Optional)</label>
                          <Input.TextArea
                            placeholder="Enter Welcome Message"
                            value={welcomeMessage}
                            disabled={loading}
                            onChange={(e) => setWelcomeMessage(e.target.value)}
                          />
                          <Typography.Text type="secondary">
                            The welcome message is displayed in the chat window
                            when the user joins the session.
                          </Typography.Text>
                        </div>
                      </Space>
                    ),
                  },
                ]}
              />
              <Divider />
              {/* advanced settings */}
              <Collapse
                bordered={false}
                items={[
                  {
                    key: "2",
                    label: (
                      <h4 style={{ margin: 0 }}>
                        Advanced Settings
                      </h4>
                    ),
                    children: (
                      <Space direction="vertical" size="large" style={{ width: "100%" }}>
                        {/* enable_moderator_to_unmute_users */}
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                          <label>Enable Moderator To Unmute Users</label>
                          <Switch
                            disabled={loading}
                            checked={enableModeratorToUnmuteUsers}
                            onChange={(checked) => setEnableModeratorToUnmuteUsers(checked)}
                          />
                        </div>

                        {/* skip_check_audio */}
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                          <label>Skip Check Audio</label>
                          <Switch
                            disabled={loading}
                            checked={skipCheckAudio}
                            onChange={(checked) => setSkipCheckAudio(checked)}
                          />
                        </div>

                        {/* disable_listen_only_mode */}
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                          <label>Disable Listen Only Mode</label>
                          <Switch
                            disabled={loading}
                            checked={disableListenOnlyMode}
                            onChange={(checked) => setDisableListenOnlyMode(checked)}
                          />
                        </div>

                        {/* enable_user_private_chats */}
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                          <label>Enable User Private Chats</label>
                          <Switch
                            disabled={loading}
                            checked={enableUserPrivateChats}
                            onChange={(checked) => setEnableUserPrivateChats(checked)}
                          />
                        </div>

                        {/* class_layout */}
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                          <label>Class Layout</label>
                          <select
                            disabled={loading}
                            value={classLayout}
                            onChange={(e) => setClassLayout(e.target.value)}
                          >
                            <option value="SMART_LAYOUT">Default</option>
                            <option value="PRESENTATION_FOCUS">Presentation Focused</option>
                            <option value="VIDEO_FOCUS">Video Focused</option>
                          </select>
                        </div>

                        {/* additional_join_params */}
                        <div>
                          <label>Additional Join Params(Optional)</label>
                          <Input.TextArea
                            placeholder='{"webcamsOnlyForModerator":true, "bannerText":"",...}'
                            value={additionalJoinParams}
                            disabled={loading}
                            onChange={(e) => setAdditionalJoinParams(e.target.value)}
                          />
                          <Typography.Text type="secondary">
                            You can enter additional parameters to customize how
                            a class is created (
                            <a
                              href="https://docs.bigbluebutton.org/development/api/#create"
                              target="_blank"
                              rel="noreferrer"
                            >
                              API Doc
                            </a>
                            )
                          </Typography.Text>
                        </div>
                      </Space>
                    ),
                  },
                ]}
              />
            </div>
          </Space>
        </section>
      </div>
      {
        error && (
          <Typography.Text type="danger">{error}</Typography.Text>
        ) /* error message */
      }
    </Modal>
  );
};
export default EditClassModal;
